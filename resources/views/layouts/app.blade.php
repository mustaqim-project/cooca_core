<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Cooca UMKM' }} — cooca.id</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">

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
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- AppAlert (Centralized Alert & Confirm System) -->
    <script src="{{ asset('js/app-alert.js') }}"></script>
    <style>
        .swal2-popup {
            background: #0f172a !important;
            border: 1px solid #334155 !important;
            border-radius: 1.25rem !important;
            color: #f8fafc !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7) !important;
        }
        .swal2-title {
            color: #ffffff !important;
            font-size: 1.15rem !important;
            font-weight: 800 !important;
        }
        .swal2-html-container {
            color: #94a3b8 !important;
            font-size: 0.875rem !important;
        }
        .swal2-confirm {
            background-color: #10b981 !important;
            color: #020617 !important;
            font-weight: 800 !important;
            border-radius: 0.75rem !important;
            padding: 0.625rem 1.25rem !important;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.25) !important;
        }
        .swal2-cancel {
            background-color: #334155 !important;
            color: #e2e8f0 !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            padding: 0.625rem 1.25rem !important;
        }
    </style>

    <style>
        html,
        body {
            overflow-x: hidden;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .glass-nav {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .glass-header {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .glass-card-interactive {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.2s ease-in-out;
        }

        .glass-card-interactive:hover {
            border-color: rgba(34, 197, 94, 0.4);
            transform: translateY(-2px);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        /* Aturan global untuk truncate judul topbar */
        .app-topbar-title h1,
        .app-topbar-title p {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 1023px) {
            .app-sidebar {
                width: min(18rem, 86vw);
            }

            .app-topbar {
                min-height: 4.25rem;
                height: auto;
                padding: .75rem 1rem;
                gap: .75rem;
            }

            .app-topbar-title {
                flex: 1 1 auto;
                min-width: 0;
                overflow: hidden;
            }

            .app-topbar-actions {
                flex: 0 0 auto;
                min-width: 0;
                flex-wrap: nowrap;
            }
        }

        @media (max-width: 639px) {
            .app-topbar-title h1 {
                font-size: .875rem;
                max-width: 45vw;
            }

            .app-topbar-actions .relative>button {
                max-width: 7rem;
                padding-left: .55rem;
                padding-right: .55rem;
            }

            .app-topbar-actions .plan-pill-detail,
            .app-topbar-actions .upgrade-link {
                display: none;
            }

            .app-topbar-actions {
                gap: .5rem;
            }
        }

        @media (max-width: 360px) {
            .app-topbar-title h1 {
                max-width: 40vw;
                font-size: .8rem;
            }

            .app-topbar-actions .relative>button {
                max-width: 6rem;
            }
        }
    </style>
</head>

<body class="h-full bg-slate-950 text-slate-100 antialiased" x-data="{
    sidebarOpen: false,
    comingSoonOpen: false,
    comingSoonFeature: { title: '', icon: 'sparkles', desc: '', color: 'purple' },
    openComingSoon(feature) {
        this.comingSoonFeature = {
            title: feature?.title || 'Fitur Baru',
            icon: feature?.icon || 'sparkles',
            desc: feature?.desc || '',
            color: feature?.color || 'purple'
        };
        this.comingSoonOpen = true;
    },
    init() {
        window.addEventListener('tour-open-sidebar', () => { this.sidebarOpen = true; });
        window.addEventListener('tour-close-sidebar', () => { this.sidebarOpen = false; });
        window.addEventListener('cooca-coming-soon', (e) => { this.openComingSoon(e.detail); });
    }
}">
    <div class="min-h-full flex flex-col lg:flex-row">

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/80 z-40 lg:hidden"
            @click="sidebarOpen = false" style="display: none;"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="app-sidebar fixed inset-y-0 left-0 z-50 w-72 h-screen max-h-screen glass-nav flex flex-col justify-between transition-transform duration-300 ease-in-out">

            <!-- Brand Logo -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800 shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div id="tour-active-business"
                        class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        <i data-lucide="boxes" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <div class="font-extrabold text-lg text-white tracking-tight leading-none">Cooca UMKM</div>
                    </div>
                </a>
                <button @click="sidebarOpen = false"
                    class="lg:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Active Tenant Card -->
            @php
                $activeBiz = \App\Support\Context::business();
                $navEntitlement = app(\App\Domain\Billing\EntitlementService::class);
                $navUsage = $activeBiz ? $navEntitlement->getUsageSummary($activeBiz) : null;
                $isCorePlan = $navUsage['is_core'] ?? false;
            @endphp
            @if ($activeBiz)
                <div class="px-4 py-3 border-b border-slate-800/80 shrink-0">
                    <div
                        class="p-3 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3 overflow-hidden">
                            @if ($activeBiz->logo_url)
                                <div
                                    class="w-8 h-8 rounded-lg bg-white/5 border border-slate-700 flex items-center justify-center shrink-0 overflow-hidden p-0.5">
                                    <img src="{{ $activeBiz->logo_url }}" alt="{{ $activeBiz->name }}"
                                        class="w-full h-full object-contain">
                                </div>
                            @else
                                <div
                                    class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center shrink-0">
                                    <i data-lucide="building-2" class="w-4 h-4 text-emerald-400"></i>
                                </div>
                            @endif
                            <div class="overflow-hidden">
                                <div class="text-xs text-slate-400 font-medium">Bisnis Aktif</div>
                                <div class="text-sm font-bold text-white truncate">{{ $activeBiz->name }}</div>
                            </div>
                        </div>
                        <a href="{{ route('businesses.select') }}" id="tour-switch-business" title="Ganti Bisnis"
                            class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-emerald-400 transition-colors">
                            <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Navigation Links (Scrollable Container) -->
            @php
                $activeBiz = \App\Support\Context::business();
                $navEntitlement = app(\App\Domain\Billing\EntitlementService::class);
                $navUsage = $activeBiz ? $navEntitlement->getUsageSummary($activeBiz) : null;
                $isCorePlan = $navUsage['is_core'] ?? false;

                $isSalesRoute =
                    request()->routeIs('pos.*') ||
                    request()->routeIs('invoices.*') ||
                    request()->routeIs('sales.*') ||
                    request()->routeIs('customers.*') ||
                    request()->routeIs('crm.*');
                $isPurchasingRoute =
                    request()->routeIs('purchasing.*') ||
                    request()->routeIs('purchase-orders.*') ||
                    request()->routeIs('purchase.returns.*');
                $isMasterDataRoute =
                    request()->routeIs('suppliers.*') ||
                    request()->routeIs('material-categories.*') ||
                    request()->routeIs('product-categories.*') ||
                    request()->routeIs('units.*');
                $isInventoryRoute =
                    request()->routeIs('products.*') ||
                    request()->routeIs('materials.*') ||
                    request()->routeIs('inventory.*') ||
                    request()->routeIs('warehouse.*');
                $isCostingRoute =
                    request()->routeIs('calculator.*') ||
                    request()->routeIs('labor-machines.*') ||
                    request()->routeIs('profitability.*') ||
                    request()->routeIs('simulator.*');
                $isWhatsAppRoute = request()->routeIs('whatsapp.*');
                $isFinanceRoute = request()->routeIs('finance.*');
                $isReportsRoute = request()->routeIs('reports.*') || request()->routeIs('pos.reports.*');
                $isSettingsRoute =
                    request()->routeIs('settings.*') ||
                    request()->routeIs('billing.*') ||
                    request()->routeIs('community.*') ||
                    request()->routeIs('feedback.*');

                // Permission aggregations for accordion visibility
                $canAccessSales =
                    \App\Support\Context::hasPermission('pos.terminal') ||
                    \App\Support\Context::hasPermission('invoices.view') ||
                    \App\Support\Context::hasPermission('sales.view') ||
                    \App\Support\Context::hasPermission('customers.view');
                $canAccessPurchasing =
                    \App\Support\Context::hasPermission('purchasing.view') ||
                    \App\Support\Context::hasPermission('receiving.manage');
                $canAccessInventory =
                    \App\Support\Context::hasPermission('products.view') ||
                    \App\Support\Context::hasPermission('materials.view') ||
                    \App\Support\Context::hasPermission('inventory.view') ||
                    \App\Support\Context::hasPermission('inventory.manage');
                $canAccessCosting =
                    \App\Support\Context::hasPermission('costing.view_margin') ||
                    \App\Support\Context::hasPermission('costing.manage') ||
                    \App\Support\Context::hasPermission('labor_machines.view');
                $canAccessFinance =
                    \App\Support\Context::hasPermission('accounting.view') ||
                    \App\Support\Context::hasPermission('expenses.view');
                $canAccessReports =
                    \App\Support\Context::hasPermission('reports.view') ||
                    \App\Support\Context::hasPermission('pos.reports');
                $canAccessSettings =
                    \App\Support\Context::hasPermission('settings.view') ||
                    \App\Support\Context::hasPermission('roles.view') ||
                    \App\Support\Context::hasPermission('billing.view') ||
                    \App\Support\Context::isOwner();
                $canAccessMasterData =
                    \App\Support\Context::hasPermission('master_data.suppliers.view') ||
                    \App\Support\Context::hasPermission('master_data.material_categories.view') ||
                    \App\Support\Context::hasPermission('master_data.product_categories.view') ||
                    \App\Support\Context::hasPermission('master_data.units.view');
            @endphp

            <nav class="flex-1 overflow-y-auto px-3.5 py-3 space-y-2.5 min-h-0 overscroll-contain text-xs"
                x-data="{
                    salesOpen: {{ $isSalesRoute ? 'true' : 'false' }},
                    whatsappOpen: {{ $isWhatsAppRoute ? 'true' : 'false' }},
                    purchasingOpen: {{ $isPurchasingRoute ? 'true' : 'false' }},
                    masterDataOpen: {{ $isMasterDataRoute ? 'true' : 'false' }},
                    inventoryOpen: {{ $isInventoryRoute ? 'true' : 'false' }},
                    costingOpen: {{ $isCostingRoute ? 'true' : 'false' }},
                    financeOpen: {{ $isFinanceRoute ? 'true' : 'false' }},
                    reportsOpen: {{ $isReportsRoute ? 'true' : 'false' }}
                }">

                <!-- 1. RINGKASAN & INTI (DASHBOARD & AI) -->
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" id="tour-nav-dashboard"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 shadow-sm' : 'text-slate-300 hover:bg-slate-900/80 hover:text-white' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 text-emerald-400"></i>
                        <span>Dashboard</span>
                    </a>

                    @if (\App\Support\Context::hasPermission('ai.access'))
                        <a href="#" id="tour-nav-ai" @click.prevent="openComingSoon({ title: 'AI Assistant', icon: 'bot', desc: 'Fitur AI Cockpit untuk analisis penjualan, prediksi tren, dan asisten pintar kasir POS berbasis Gemini AI.', color: 'purple' })"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('pos.ai.*') ? 'bg-purple-500/15 text-purple-300 border border-purple-500/30 shadow-sm' : 'text-slate-300 hover:bg-slate-900/80 hover:text-white' }}">
                            <i data-lucide="bot" class="w-4 h-4 text-purple-400"></i>
                            <span>AI Assistant</span>
                            <span class="ml-auto text-[9px] px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 font-black border border-purple-500/30">Segera</span>
                        </a>
                    @endif

                    @if (\App\Support\Context::isOwner())
                        <a href="#" id="tour-nav-community" @click.prevent="openComingSoon({ title: 'Komunitas Owner', icon: 'users', desc: 'Forum diskusi eksklusif khusus para owner UMKM Cooca — berbagi tips, trik, dan strategi bisnis bersama.', color: 'amber' })"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('community.*') ? 'bg-amber-500/15 text-amber-300 border border-amber-500/30 shadow-sm' : 'text-slate-300 hover:bg-slate-900/80 hover:text-white' }}">
                            <i data-lucide="users" class="w-4 h-4 text-amber-400"></i>
                            <span>Komunitas Owner</span>
                            <span class="ml-auto text-[9px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-black">Segera</span>
                        </a>
                    @endif
                </div>

                <!-- 3B. WEBSITE & LANDING PAGE (CMS & PUBLIC PAGE) -->
                <div class="space-y-1">
                    <a href="{{ route('landing-page.edit') }}" id="tour-nav-landing-page"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('landing-page.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 shadow-sm' : 'text-slate-300 hover:bg-slate-900/80 hover:text-white' }}">
                        <i data-lucide="globe" class="w-4 h-4 text-emerald-400"></i>
                        <span>Landing Page</span>
                        <span class="ml-auto text-[9px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-black border border-emerald-500/30">Live CMS</span>
                    </a>
                </div>

                                <!-- 3. WHATSAPP GATEWAY (BOT & PROMOSI) -->
                <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 overflow-hidden">
                    <button type="button" @click="whatsappOpen = !whatsappOpen"
                        class="w-full flex items-center justify-between px-3 py-2.5 text-left font-semibold text-slate-300 hover:text-white hover:bg-slate-900/50 transition"
                        :class="whatsappOpen ? 'bg-emerald-500/10 text-white' : ''">
                        <div class="flex items-center gap-2.5">
                            <div class="w-4 h-4 rounded flex items-center justify-center text-[#25D366]">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0zm0 21.785a9.874 9.874 0 0 1-5.032-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.861 9.861 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884z"/></svg>
                            </div>
                            <span class="text-xs">WhatsApp Gateway</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-[#25D366]/15 text-[#25D366] font-extrabold border border-[#25D366]/30">Scan WA</span>
                            <i data-lucide="chevron-down"
                                class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200"
                                :class="whatsappOpen ? 'rotate-180 text-[#25D366]' : ''"></i>
                        </div>
                    </button>

                    <div :class="whatsappOpen ? '' : 'hidden'"
                        class="px-2 pb-2 pt-1 space-y-0.5 border-t border-slate-900">
                        <a href="{{ route('whatsapp.index') }}"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('whatsapp.index') ? 'bg-[#25D366]/15 text-[#25D366] font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="qr-code" class="w-3.5 h-3.5 text-[#25D366]"></i>
                            <span>Scan QR / Status</span>
                        </a>
                        <a href="{{ route('whatsapp.broadcast.index') }}"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('whatsapp.broadcast.*') ? 'bg-[#25D366]/15 text-[#25D366] font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="send" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>Blast Promosi</span>
                        </a>
                        <a href="{{ route('whatsapp.logs.index') }}"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('whatsapp.logs.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="list" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Log Pesan Struk</span>
                        </a>
                    </div>
                </div>


                <!-- 2. PENJUALAN (COLLAPSIBLE) -->
                @if ($canAccessSales)
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 overflow-hidden">
                        <button type="button" id="tour-group-sales" data-tour-group="sales" @click="salesOpen = !salesOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-left font-semibold text-slate-300 hover:text-white hover:bg-slate-900/50 transition">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="shopping-cart" class="w-4 h-4 text-teal-400"></i>
                                <span class="text-xs">Kasir &amp; Penjualan</span>
                            </div>
                            <i data-lucide="chevron-down"
                                class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200"
                                :class="salesOpen ? 'rotate-180 text-teal-400' : ''"></i>
                        </button>

                        <div x-show="salesOpen" x-transition.opacity
                            class="px-2 pb-2 space-y-0.5 pt-1 border-t border-slate-900">
                            @if (\App\Support\Context::hasPermission('pos.terminal'))
                                <a href="{{ route('pos.terminal') }}" id="tour-nav-pos-terminal"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('pos.terminal') ? 'bg-teal-500/15 text-teal-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="calculator" class="w-3.5 h-3.5 text-teal-400"></i>
                                    <span>Terminal Kasir POS</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('invoices.view'))
                                <a href="{{ route('invoices.index') }}" id="tour-nav-invoices"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('invoices.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>Faktur &amp; Piutang</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('pos.terminal') || \App\Support\Context::hasPermission('pos.reports'))
                                <a href="{{ route('pos.orders.index') }}" id="tour-nav-pos-orders"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('pos.orders.*') || request()->routeIs('pos.shifts.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>Riwayat Transaksi &amp; Shift</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('customers.view'))
                                <a href="{{ route('customers.index') }}" id="tour-nav-customers"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('customers.*') || request()->routeIs('crm.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-400"></i>
                                    <span>Pelanggan &amp; CRM</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('sales.view'))
                                <a href="{{ route('sales.orders.index') }}" id="tour-nav-sales-orders"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('sales.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="check-square" class="w-3.5 h-3.5 text-cyan-400"></i>
                                    <span>Pesanan &amp; Penawaran</span>
                                </a>

                                <a href="{{ route('sales.returns.index') }}" id="tour-nav-sales-returns"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('sales.returns.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="undo-2" class="w-3.5 h-3.5 text-rose-400"></i>
                                    <span>Retur Penjualan</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif


                

                <!-- 4. MASTER DATA CMS (COLLAPSIBLE) -->
                @if ($canAccessMasterData)
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 overflow-hidden">
                        <button type="button" id="tour-group-master-data" data-tour-group="master-data" @click="masterDataOpen = !masterDataOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-left font-semibold text-slate-300 hover:text-white hover:bg-slate-900/50 transition"
                            :class="masterDataOpen ? 'bg-emerald-500/10 text-white' : ''">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="database" class="w-4 h-4 text-emerald-400"></i>
                                <span class="text-xs">Master Data</span>
                            </div>
                            <i data-lucide="chevron-down"
                                class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200"
                                :class="masterDataOpen ? 'rotate-180 text-emerald-400' : ''"></i>
                        </button>

                        <div :class="masterDataOpen ? '' : 'hidden'"
                            class="px-2 pb-2 pt-1 space-y-0.5 border-t border-slate-900">
                            @if (\App\Support\Context::hasPermission('master_data.suppliers.view'))
                                <a href="{{ route('suppliers.index') }}" id="tour-nav-suppliers"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('suppliers.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="truck" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>Supplier</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('master_data.material_categories.view'))
                                <a href="{{ route('material-categories.index') }}" id="tour-nav-material-categories"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('material-categories.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="layers" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>Kategori Bahan</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('master_data.product_categories.view'))
                                <a href="{{ route('product-categories.index') }}" id="tour-nav-product-categories"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('product-categories.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="folder" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>Kategori Produk</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('master_data.units.view'))
                                <a href="{{ route('units.index') }}" id="tour-nav-units"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('units.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="scale" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>Satuan &amp; Konversi</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- 4. PEMBELIAN (COLLAPSIBLE) -->
                @if ($canAccessPurchasing)
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 overflow-hidden">
                        <button type="button" id="tour-group-purchasing" data-tour-group="purchasing" @click="purchasingOpen = !purchasingOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-left font-semibold text-slate-300 hover:text-white hover:bg-slate-900/50 transition">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="truck" class="w-4 h-4 text-amber-400"></i>
                                <span class="text-xs">Pembelian &amp; Vendor</span>
                            </div>
                            <i data-lucide="chevron-down"
                                class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200"
                                :class="purchasingOpen ? 'rotate-180 text-amber-400' : ''"></i>
                        </button>

                        <div x-show="purchasingOpen" x-transition.opacity
                            class="px-2 pb-2 space-y-0.5 pt-1 border-t border-slate-900">
                            @if (\App\Support\Context::hasPermission('purchasing.view'))
                                <a href="{{ route('purchase-orders.index') }}" id="tour-nav-purchase-orders"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('purchase-orders.*') ? 'bg-amber-500/15 text-amber-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-amber-400"></i>
                                    <span>Purchase Order (PO)</span>
                                </a>

                                <a href="{{ route('purchasing.bills.index') }}" id="tour-nav-purchasing-bills"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('purchasing.bills.*') ? 'bg-amber-500/15 text-amber-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-amber-400"></i>
                                    <span>Tagihan &amp; Hutang Supplier</span>
                                </a>

                                <a href="{{ route('purchase.returns.index') }}" id="tour-nav-purchase-returns"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('purchase.returns.*') ? 'bg-amber-500/15 text-amber-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="corner-up-left" class="w-3.5 h-3.5 text-rose-400"></i>
                                    <span>Retur Pembelian</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- 5. PERSEDIAAN (COLLAPSIBLE) -->
                @if ($canAccessInventory)
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 overflow-hidden">
                        <button type="button" id="tour-group-inventory" data-tour-group="inventory" @click="inventoryOpen = !inventoryOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-left font-semibold text-slate-300 hover:text-white hover:bg-slate-900/50 transition">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="package" class="w-4 h-4 text-emerald-400"></i>
                                <span class="text-xs">Produk &amp; Inventori</span>
                            </div>
                            <i data-lucide="chevron-down"
                                class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200"
                                :class="inventoryOpen ? 'rotate-180 text-emerald-400' : ''"></i>
                        </button>

                        <div x-show="inventoryOpen" x-transition.opacity
                            class="px-2 pb-2 space-y-0.5 pt-1 border-t border-slate-900">
                            @if (\App\Support\Context::hasPermission('inventory.view') || \App\Support\Context::hasPermission('inventory.manage'))
                                <a href="{{ route('warehouse.index') }}" id="tour-nav-warehouse"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('warehouse.*') ? 'bg-cyan-500/15 text-cyan-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="warehouse" class="w-3.5 h-3.5 text-cyan-400"></i>
                                    <span>Gudang &amp; Lokasi</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('products.view'))
                                <a href="{{ route('products.index') }}" id="tour-nav-products"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('products.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="package" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>Katalog Produk &amp; Resep</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('materials.view'))
                                <a href="{{ route('materials.index') }}" id="tour-nav-materials"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('materials.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="boxes" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>Bahan Baku &amp; Harga</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('inventory.view'))
                                <a href="{{ route('inventory.stocks') }}" id="tour-nav-inventory-stocks"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('inventory.stocks') ? 'bg-cyan-500/15 text-cyan-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="layers" class="w-3.5 h-3.5 text-cyan-400"></i>
                                    <span>Stok Real-Time</span>
                                </a>

                                <a href="{{ route('inventory.movements') }}" id="tour-nav-inventory-movements"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('inventory.movements') ? 'bg-cyan-500/15 text-cyan-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="arrow-left-right" class="w-3.5 h-3.5 text-cyan-400"></i>
                                    <span>Mutasi Stok (Kartu Stok)</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('inventory.manage'))
                                <a href="{{ route('inventory.transfers.index') }}" id="tour-nav-inventory-transfers"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('inventory.transfers.*') ? 'bg-cyan-500/15 text-cyan-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="repeat" class="w-3.5 h-3.5 text-cyan-400"></i>
                                    <span>Transfer Stok Gudang</span>
                                </a>

                                <a href="{{ route('inventory.opnames.index') }}" id="tour-nav-inventory-opnames"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('inventory.opnames.*') ? 'bg-cyan-500/15 text-cyan-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-cyan-400"></i>
                                    <span>Stock Opname Fisik</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- 6. HPP & PRODUKSI (COLLAPSIBLE) -->
                @if ($canAccessCosting)
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 overflow-hidden">
                        <button type="button" id="tour-group-costing" data-tour-group="costing" @click="costingOpen = !costingOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-left font-semibold text-slate-300 hover:text-white hover:bg-slate-900/50 transition">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="calculator" class="w-4 h-4 text-emerald-400"></i>
                                <span class="text-xs">HPP &amp; Produksi</span>
                            </div>
                            <i data-lucide="chevron-down"
                                class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200"
                                :class="costingOpen ? 'rotate-180 text-emerald-400' : ''"></i>
                        </button>

                        <div x-show="costingOpen" x-transition.opacity
                            class="px-2 pb-2 space-y-0.5 pt-1 border-t border-slate-900">
                            @if (\App\Support\Context::hasPermission('costing.view_margin'))
                                <a href="{{ route('calculator.index') }}" id="tour-nav-calculator"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('calculator.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>Kalkulator HPP 3-Pilar</span>
                                </a>
                            @endif

                            @if (
                                \App\Support\Context::hasPermission('labor_machines.view') ||
                                    \App\Support\Context::hasPermission('costing.view_margin'))
                                <a href="{{ route('labor-machines.index') }}" id="tour-nav-labor-machines"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('labor-machines.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="users-2" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>Upah Kerja &amp; Mesin</span>
                                </a>
                            @endif

                            @if (
                                \App\Support\Context::hasPermission('costing.view_margin') ||
                                    \App\Support\Context::hasPermission('reports.costing'))
                                <a href="{{ route('profitability.index') }}" id="tour-nav-profitability"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('profitability.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="target" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>BEP &amp; Profitabilitas</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('costing.view_margin'))
                                <a href="{{ route('simulator.index') }}" id="tour-nav-simulator"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('simulator.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="sliders" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>Simulasi What-If</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- 7. KEUANGAN (COLLAPSIBLE) -->
                @if ($canAccessFinance)
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 overflow-hidden">
                        <button type="button" id="tour-group-finance" data-tour-group="finance" @click="financeOpen = !financeOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-left font-semibold text-slate-300 hover:text-white hover:bg-slate-900/50 transition">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="landmark" class="w-4 h-4 text-amber-400"></i>
                                <span class="text-xs">Keuangan &amp; Kas</span>
                            </div>
                            <i data-lucide="chevron-down"
                                class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200"
                                :class="financeOpen ? 'rotate-180 text-amber-400' : ''"></i>
                        </button>

                        <div x-show="financeOpen" x-transition.opacity
                            class="px-2 pb-2 space-y-0.5 pt-1 border-t border-slate-900">
                            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('expenses.view'))
                                <a href="{{ route('finance.cash-bank.index') }}" id="tour-nav-cash-bank"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('finance.cash-bank.index') ? 'bg-amber-500/15 text-amber-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="landmark" class="w-3.5 h-3.5 text-amber-400"></i>
                                    <span>Kas &amp; Rekening Bank</span>
                                </a>

                                <a href="{{ route('finance.cash-bank.ledger') }}" id="tour-nav-cash-ledger"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('finance.cash-bank.ledger') ? 'bg-amber-500/15 text-amber-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="book" class="w-3.5 h-3.5 text-amber-400"></i>
                                    <span>Buku Kas &amp; Ledger</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('expenses.view'))
                                <a href="{{ route('finance.expenses.index') }}" id="tour-nav-expenses"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('finance.expenses.*') ? 'bg-rose-500/15 text-rose-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="wallet" class="w-3.5 h-3.5 text-rose-400"></i>
                                    <span>Beban Operasional</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('invoices.view'))
                                <a href="{{ route('finance.receivables') }}" id="tour-nav-receivables"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('finance.receivables') ? 'bg-cyan-500/15 text-cyan-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="arrow-down-left" class="w-3.5 h-3.5 text-cyan-400"></i>
                                    <span>Piutang Usaha (AR Aging)</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('purchasing.view'))
                                <a href="{{ route('finance.payables') }}" id="tour-nav-payables"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('finance.payables') ? 'bg-amber-500/15 text-amber-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-amber-400"></i>
                                    <span>Hutang Usaha (AP Aging)</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('accounting.view'))
                                <a href="{{ route('finance.journals.index') }}" id="tour-nav-journals"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('finance.journals.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="book-open" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>Jurnal Akuntansi Otomatis</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- 8. LAPORAN & ANALITIK (COLLAPSIBLE) -->
                @if ($canAccessReports)
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 overflow-hidden">
                        <button type="button" id="tour-group-reports" data-tour-group="reports" @click="reportsOpen = !reportsOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-left font-semibold text-slate-300 hover:text-white hover:bg-slate-900/50 transition">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="bar-chart-3" class="w-4 h-4 text-amber-400"></i>
                                <span class="text-xs">Laporan &amp; Analitik</span>
                            </div>
                            <i data-lucide="chevron-down"
                                class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200"
                                :class="reportsOpen ? 'rotate-180 text-amber-400' : ''"></i>
                        </button>

                        <div x-show="reportsOpen" x-transition.opacity
                            class="px-2 pb-2 space-y-0.5 pt-1 border-t border-slate-900">
                            @if (\App\Support\Context::hasPermission('reports.view'))
                                <a href="{{ route('reports.index') }}" id="tour-nav-reports"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-amber-500/15 text-amber-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="bar-chart-3" class="w-3.5 h-3.5 text-amber-400"></i>
                                    <span>Laporan &amp; Analitik Bisnis</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('pos.reports'))
                                <a href="{{ route('pos.reports.index') }}" id="tour-nav-pos-reports"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('pos.reports.*') ? 'bg-amber-500/15 text-amber-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                                    <i data-lucide="file-pie-chart" class="w-3.5 h-3.5 text-teal-400"></i>
                                    <span>Laporan Kasir POS</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- 9. PENGATURAN & LISENSI -->
                @if ($canAccessSettings)
                    <div class="space-y-1 pt-1">
                        @if (\App\Support\Context::hasPermission('settings.view'))
                            <a href="{{ route('settings.index') }}" id="tour-nav-settings"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition-all {{ request()->routeIs('settings.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                                <i data-lucide="settings" class="w-4 h-4 text-slate-400"></i>
                                <span>Pengaturan Toko &amp; Tim</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('roles.view'))
                            <a href="{{ route('settings.roles.index') }}" id="tour-nav-roles"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition-all {{ request()->routeIs('settings.roles.*') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                                <i data-lucide="shield-check" class="w-4 h-4 text-cyan-400"></i>
                                <span>Kontrol Akses &amp; Role</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('billing.view'))
                            <a href="{{ route('billing.limits') }}" id="tour-nav-billing"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition-all {{ request()->routeIs('billing.*') ? 'bg-purple-500/15 text-purple-300 border border-purple-500/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                                <i data-lucide="sparkles" class="w-4 h-4 text-purple-400"></i>
                                <span>Paket &amp; Kuota</span>
                                <span
                                    class="ml-auto text-[9px] px-2 py-0.5 rounded-full {{ $isCorePlan ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }} font-black uppercase">
                                    {{ $isCorePlan ? 'Core' : 'Free' }}
                                </span>
                            </a>
                        @endif

                        @if (\App\Support\Context::isOwner())
                            <a href="{{ route('feedback.bugs.index') }}" id="tour-nav-feedback"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition-all {{ request()->routeIs('feedback.*') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                                <i data-lucide="life-buoy" class="w-4 h-4 text-cyan-400"></i>
                                <span>Dukungan Produk</span>
                            </a>
                        @endif
                    </div>
                @endif

            </nav>

            <!-- User Profile Bottom Bar (Sticky at Bottom) -->
            <div class="p-3 border-t border-slate-800/80 space-y-2 shrink-0 bg-slate-950/90">
                <form method="POST" action="{{ route('onboarding.restart') }}">
                    @csrf
                    <button type="submit"
                        class="w-full px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-[11px] font-semibold flex items-center justify-center gap-1.5 transition-colors">
                        <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                        <span>Mulai Ulang Panduan Tour</span>
                    </button>
                </form>

                <div class="flex items-center justify-between">
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-2.5 overflow-hidden group flex-1 mr-2 p-1 rounded-xl hover:bg-slate-900 transition-colors">
                        <div
                            class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-emerald-400 shrink-0 group-hover:border-emerald-500/50">
                            {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                        </div>
                        <div class="overflow-hidden text-left">
                            <div
                                class="text-xs font-semibold text-white truncate group-hover:text-emerald-400 transition-colors">
                                {{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-[10px] text-slate-400 truncate flex items-center gap-1">
                                <i data-lucide="key" class="w-3 h-3 text-emerald-400"></i>
                                <span>Profil &amp; Sandi</span>
                            </div>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout"
                            class="p-2 hover:bg-red-500/10 text-slate-400 hover:text-red-400 rounded-lg transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 lg:pl-72 flex flex-col min-h-screen">

            <!-- Topbar Header -->
            <header
                class="app-topbar h-20 glass-header sticky top-0 z-30 flex items-center justify-between px-6 lg:px-10">
                <div class="app-topbar-title flex items-center gap-4 min-w-0 flex-1">
                    <button id="tour-mobile-menu-btn"
                        @click="sidebarOpen = true; window.dispatchEvent(new CustomEvent('sidebar-opened'))"
                        class="lg:hidden p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 shrink-0">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <div class="min-w-0">
                        <h1 class="text-lg font-bold text-white truncate">{{ $headerTitle ?? 'Cooca UMKM' }}</h1>
<p class="text-xs text-slate-400 hidden sm:block truncate">
                            {{ $headerSubtitle ?? 'Sistem Perhitungan HPP & Manajemen Komersial Terintegrasi' }}</p>
                    </div>
                </div>

                <div class="app-topbar-actions flex items-center gap-2.5 flex-shrink-0">
                    @if ($activeBiz && $navUsage)
                        <!-- Plan Tracking & Subscription Status Popover -->
                        <div class="relative" x-data="{ planDropdownOpen: false }" @click.outside="planDropdownOpen = false">
                            @if ($isCorePlan)
                                <!-- Core Plan Active Pill -->
                                <button @click="planDropdownOpen = !planDropdownOpen" type="button"
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-950/40 hover:bg-emerald-900/40 border border-emerald-500/40 text-xs text-white transition shadow-sm group">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400"></span>
                                    <span class="font-black text-emerald-300">Patungan Aktif</span>
                                    @if (($navUsage['ai_tokens']['remaining'] ?? 0) > 0)
                                        <span class="plan-pill-detail hidden md:inline text-[10px] text-amber-300/90 font-mono">({{ number_format(($navUsage['ai_tokens']['remaining'] ?? 0) / 1000, 0) }}k AI)</span>
                                    @endif
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-emerald-400 transition-transform duration-200" :class="planDropdownOpen ? 'rotate-180' : ''"></i>
                                </button>
                            @else
                                <!-- Free Plan Pill with Upgrade CTA -->
                                <div class="flex items-center gap-1.5">
                                    <button @click="planDropdownOpen = !planDropdownOpen" type="button"
                                        class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900/90 hover:bg-slate-800 border border-amber-500/40 text-xs text-slate-200 transition shadow-sm group">
                                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                        <span class="font-bold text-amber-300">Free Plan</span>
                                        <span class="hidden sm:inline text-[10px] text-slate-400 font-mono">({{ $navUsage['products']['used'] ?? 0 }}/50 Prod • {{ $navUsage['invoices_this_month']['used'] ?? 0 }}/10 Inv)</span>
                                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="planDropdownOpen ? 'rotate-180' : ''"></i>
                                    </button>

                                    <a href="{{ route('billing.patungan') }}"
                                        class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 text-xs font-black shadow-md shadow-emerald-500/20 transition-all hover:scale-105 active:scale-95">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                        <span>Ikut Patungan</span>
                                    </a>
                                </div>
                            @endif

                            <!-- Dropdown Panel -->
                            <div x-show="planDropdownOpen" x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                class="absolute right-0 mt-2 w-96 max-w-[92vw] rounded-3xl bg-slate-950/95 backdrop-blur-xl border border-slate-800 shadow-2xl p-5 space-y-4 z-50 text-slate-200"
                                style="display: none;">

                                <!-- Header status -->
                                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                    <div>
                                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Status Langganan</span>
                                        <h4 class="font-black text-sm text-white flex items-center gap-1.5 mt-0.5">
                                            @if ($isCorePlan)
                                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                                                <span class="text-emerald-300">{{ $navUsage['plan_label'] ?? 'Cooca UMKM (Patungan)' }}</span>
                                            @else
                                                <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                                                <span>Paket Free (Solo)</span>
                                            @endif
                                        </h4>
                                        @if ($isCorePlan && !empty($navUsage['ends_at']))
                                            <p class="text-[10px] text-emerald-400/90 font-mono mt-0.5">Aktif s/d {{ $navUsage['ends_at'] }}</p>
                                        @endif
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase border {{ $isCorePlan ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-slate-800 text-slate-400 border-slate-700' }}">
                                        {{ $isCorePlan ? 'Aktif' : 'Gratis' }}
                                    </span>
                                </div>

                                <!-- Resource Usage Meters (Sesuai /billing/limits) -->
                                <div class="space-y-3.5 text-xs max-h-72 overflow-y-auto pr-1">
                                    <!-- 1. Katalog Produk -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[11px]">
                                            <span class="text-slate-400 font-medium">Katalog Produk:</span>
                                            <span class="font-mono font-bold {{ ($navUsage['products']['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">
                                                {{ $navUsage['products']['used'] ?? 0 }} / {{ $isCorePlan ? '∞ Unlimited' : ($navUsage['products']['limit'] ?? 50) }}
                                            </span>
                                        </div>
                                        @if (!$isCorePlan)
                                            <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-500 {{ ($navUsage['products']['percent'] ?? 0) >= 90 ? 'bg-rose-500' : (($navUsage['products']['percent'] ?? 0) >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                    style="width: {{ $navUsage['products']['percent'] ?? 0 }}%">
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 2. Invoice / Faktur Penjualan -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[11px]">
                                            <span class="text-slate-400 font-medium">Invoice Bulan Ini:</span>
                                            <span class="font-mono font-bold {{ ($navUsage['invoices_this_month']['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">
                                                {{ $navUsage['invoices_this_month']['used'] ?? 0 }} / {{ $isCorePlan ? '∞ Unlimited' : ($navUsage['invoices_this_month']['limit'] ?? 10) }}
                                            </span>
                                        </div>
                                        @if (!$isCorePlan)
                                            <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-500 {{ ($navUsage['invoices_this_month']['percent'] ?? 0) >= 90 ? 'bg-rose-500' : (($navUsage['invoices_this_month']['percent'] ?? 0) >= 70 ? 'bg-amber-500' : 'bg-indigo-500') }}"
                                                    style="width: {{ $navUsage['invoices_this_month']['percent'] ?? 0 }}%">
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 3. Transaksi Kasir POS -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[11px]">
                                            <span class="text-slate-400 font-medium">Transaksi Kasir POS:</span>
                                            <span class="font-mono font-bold {{ ($navUsage['pos_this_month']['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">
                                                {{ $navUsage['pos_this_month']['used'] ?? 0 }} / {{ $isCorePlan ? '∞ Unlimited' : ($navUsage['pos_this_month']['limit'] ?? 100) }}
                                            </span>
                                        </div>
                                        @if (!$isCorePlan)
                                            <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-500 {{ ($navUsage['pos_this_month']['percent'] ?? 0) >= 90 ? 'bg-rose-500' : (($navUsage['pos_this_month']['percent'] ?? 0) >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                    style="width: {{ $navUsage['pos_this_month']['percent'] ?? 0 }}%">
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 4. Purchase Orders (PO) -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[11px]">
                                            <span class="text-slate-400 font-medium">Purchase Order (PO):</span>
                                            <span class="font-mono font-bold {{ ($navUsage['po_this_month']['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">
                                                {{ $navUsage['po_this_month']['used'] ?? 0 }} / {{ $isCorePlan ? '∞ Unlimited' : ($navUsage['po_this_month']['limit'] ?? 10) }}
                                            </span>
                                        </div>
                                        @if (!$isCorePlan)
                                            <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-500 {{ ($navUsage['po_this_month']['percent'] ?? 0) >= 90 ? 'bg-rose-500' : (($navUsage['po_this_month']['percent'] ?? 0) >= 70 ? 'bg-amber-500' : 'bg-amber-500') }}"
                                                    style="width: {{ $navUsage['po_this_month']['percent'] ?? 0 }}%">
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 5. Resep & BOM -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[11px]">
                                            <span class="text-slate-400 font-medium">Resep / BOM:</span>
                                            <span class="font-mono font-bold {{ ($navUsage['recipes']['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">
                                                {{ $navUsage['recipes']['used'] ?? 0 }} / {{ $isCorePlan ? '∞ Unlimited' : ($navUsage['recipes']['limit'] ?? 20) }}
                                            </span>
                                        </div>
                                        @if (!$isCorePlan)
                                            <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-500 {{ ($navUsage['recipes']['percent'] ?? 0) >= 90 ? 'bg-rose-500' : (($navUsage['recipes']['percent'] ?? 0) >= 70 ? 'bg-amber-500' : 'bg-purple-500') }}"
                                                    style="width: {{ $navUsage['recipes']['percent'] ?? 0 }}%"></div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 6. Bahan Baku -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[11px]">
                                            <span class="text-slate-400 font-medium">Bahan Baku:</span>
                                            <span class="font-mono font-bold {{ ($navUsage['materials']['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">
                                                {{ $navUsage['materials']['used'] ?? 0 }} / {{ $isCorePlan ? '∞ Unlimited' : ($navUsage['materials']['limit'] ?? 20) }}
                                            </span>
                                        </div>
                                        @if (!$isCorePlan)
                                            <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-500 {{ ($navUsage['materials']['percent'] ?? 0) >= 90 ? 'bg-rose-500' : (($navUsage['materials']['percent'] ?? 0) >= 70 ? 'bg-amber-500' : 'bg-cyan-500') }}"
                                                    style="width: {{ $navUsage['materials']['percent'] ?? 0 }}%"></div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 7. Token Asisten AI (Top-Up) -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[11px]">
                                            <span class="text-slate-400 font-medium">Token AI (Top-up):</span>
                                            <span class="font-mono font-bold text-amber-300">
                                                {{ number_format($navUsage['ai_tokens']['remaining'] ?? 0, 0, ',', '.') }} Token
                                            </span>
                                        </div>
                                    </div>

                                    <!-- 8. Storage Cloud Owner -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[11px]">
                                            <span class="text-slate-400 font-medium">Storage Cloud:</span>
                                            <span class="font-mono font-bold text-cyan-300">
                                                {{ number_format($navUsage['storage']['used_mb'] ?? 0, 1, ',', '.') }} MB / {{ number_format($navUsage['storage']['limit_gb'] ?? 1, 1, ',', '.') }} GB
                                            </span>
                                        </div>
                                        <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                            <div class="h-full rounded-full bg-cyan-500 transition-all duration-500"
                                                style="width: {{ $navUsage['storage']['percentage'] ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                @if (!$isCorePlan)
                                    <!-- Upgrade Teaser Box -->
                                    <div class="p-3.5 rounded-2xl bg-emerald-950/30 border border-emerald-500/30 space-y-2">
                                        <div class="text-[11px] font-bold text-white flex items-center gap-1.5">
                                            <i data-lucide="zap" class="w-3.5 h-3.5 text-yellow-300"></i>
                                            <span>Program Patungan Cooca UMKM</span>
                                        </div>
                                        <p class="text-[10px] text-slate-400 leading-relaxed">
                                            Buka akses produk & resep tanpa batas, multi-gudang, dan import/export Excel lengkap.
                                        </p>
                                        <a href="{{ route('billing.patungan') }}"
                                            class="block w-full py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-center text-slate-950 text-xs font-black shadow-lg shadow-emerald-500/20 transition">
                                            Ikut Patungan Sekarang
                                        </a>
                                    </div>
                                @endif

                                <div class="pt-1 text-center border-t border-slate-800/80">
                                    <a href="{{ route('billing.limits') }}"
                                        class="text-[11px] text-emerald-400 hover:underline inline-flex items-center gap-1 font-semibold">
                                        <span>Kelola Paket & Kuota Lengkap</span>
                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($activeBiz)
                        <div
                            class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-xs text-slate-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>{{ $activeBiz->currency_code }} ({{ $activeBiz->currency_symbol }})</span>
                        </div>
                    @endif

                    <!-- Quick Action Menu Dropdown -->
                    <div class="relative" x-data="{ openQuick: false }">
                        <button type="button" @click="openQuick = !openQuick" @click.outside="openQuick = false"
                            class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-emerald-500/40 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
                            <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i>
                            <span class="hidden sm:inline">Aksi Cepat</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                        </button>

                        <div x-show="openQuick" x-transition
                            class="absolute right-0 mt-2 w-56 rounded-2xl bg-slate-950/95 backdrop-blur-xl border border-slate-800 shadow-2xl p-2 z-50 space-y-1 text-xs"
                            style="display: none;">
                            <button type="button" @click="openQuick = false; $dispatch('open-quick-expense')"
                                class="w-full px-3 py-2.5 rounded-xl hover:bg-slate-900 text-left text-slate-200 hover:text-emerald-400 flex items-center gap-2.5 transition">
                                <div class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400">
                                    <i data-lucide="receipt" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold">Catat Pengeluaran</div>
                                    <div class="text-[10px] text-slate-500">Biaya operasional kasir</div>
                                </div>
                            </button>

                            <button type="button" @click="openQuick = false; $dispatch('open-quick-stockin')"
                                class="w-full px-3 py-2.5 rounded-xl hover:bg-slate-900 text-left text-slate-200 hover:text-emerald-400 flex items-center gap-2.5 transition">
                                <div class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold">Beli Stok Langsung</div>
                                    <div class="text-[10px] text-slate-500">1-Klik tambah persediaan</div>
                                </div>
                            </button>

                            <button type="button" @click="openQuick = false; $dispatch('open-quick-material')"
                                class="w-full px-3 py-2.5 rounded-xl hover:bg-slate-900 text-left text-slate-200 hover:text-emerald-400 flex items-center gap-2.5 transition">
                                <div class="p-1.5 rounded-lg bg-blue-500/10 text-blue-400">
                                    <i data-lucide="boxes" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold">Tambah Bahan Baku</div>
                                    <div class="text-[10px] text-slate-500">Master bahan & resep</div>
                                </div>
                            </button>

                            <div class="border-t border-slate-900 my-1"></div>

                            <a href="{{ route('pos.terminal') }}"
                                class="w-full px-3 py-2.5 rounded-xl hover:bg-slate-900 text-left text-slate-200 hover:text-emerald-400 flex items-center gap-2.5 transition">
                                <div class="p-1.5 rounded-lg bg-teal-500/10 text-teal-400">
                                    <i data-lucide="store" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold">Buka Kasir POS</div>
                                    <div class="text-[10px] text-slate-500">Terminal kasir kilat</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('calculator.index') }}"
                        class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center gap-1.5 transition-all shrink-0">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Hitung HPP</span>
                    </a>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 p-6 lg:p-10 space-y-6">
                <!-- Flash Alerts -->
                @if (session('success'))
                    <div
                        class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 flex items-center gap-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 text-emerald-400"></i>
                        <div class="text-sm font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 flex items-center gap-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-red-400"></i>
                        <div class="text-sm font-medium">{{ session('error') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 space-y-1">
                        <div class="flex items-center gap-2 font-semibold text-sm">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-400"></i>
                            <span>Terdapat kesalahan input:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-0.5 pl-2 text-red-200">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- ========================================== -->
            <!-- GLOBAL ZERO-NAVIGATION AJAX MODALS & TOASTS -->
            <!-- ========================================== -->
            <div x-data="{
                toastList: [],
                showExpenseModal: false,
                showStockInModal: false,
                showMaterialModal: false,
                showMobileActionSheet: false,
                expenseForm: { name: '', amount: '', category: 'Operasional Toko', payment_method: 'cash', notes: '' },
                stockInForm: { material_id: '', product_id: '', quantity: 1, unit_cost: '', supplier_name: '', notes: '' },
                materialForm: { name: '', cost_per_unit: '', unit_id: '', category_id: '', sku: '' },
                isSubmitting: false,

                init() {
                    window.addEventListener('cooca-toast', (e) => {
                        this.addToast(e.detail.message, e.detail.type || 'success');
                    });
                    window.addEventListener('open-quick-expense', () => { this.showExpenseModal = true; });
                    window.addEventListener('open-quick-stockin', () => { this.showStockInModal = true; });
                    window.addEventListener('open-quick-material', () => { this.showMaterialModal = true; });
                    window.addEventListener('open-mobile-actions', () => { this.showMobileActionSheet = true; });
                },

                addToast(msg, type = 'success') {
                    const id = Date.now();
                    this.toastList.push({ id, msg, type });
                    setTimeout(() => {
                        this.toastList = this.toastList.filter(t => t.id !== id);
                    }, 4000);
                },

                async submitQuickExpense() {
                    if (!this.expenseForm.name || !this.expenseForm.amount) return;
                    this.isSubmitting = true;
                    try {
                        const res = await fetch('{{ route('dashboard.quick-expense') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.expenseForm)
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.addToast(data.message, 'success');
                            this.showExpenseModal = false;
                            this.expenseForm = { name: '', amount: '', category: 'Operasional Toko', payment_method: 'cash', notes: '' };
                            window.dispatchEvent(new CustomEvent('expense-added', { detail: data }));
                        } else {
                            this.addToast(data.message || 'Gagal menyimpan pengeluaran', 'error');
                        }
                    } catch (err) {
                        this.addToast('Terjadi kesalahan koneksi', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitQuickStockIn() {
                    if (!this.stockInForm.quantity || !this.stockInForm.unit_cost) return;
                    this.isSubmitting = true;
                    try {
                        const res = await fetch('{{ route('dashboard.quick-stock-in') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.stockInForm)
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.addToast(data.message, 'success');
                            this.showStockInModal = false;
                            this.stockInForm = { material_id: '', product_id: '', quantity: 1, unit_cost: '', supplier_name: '', notes: '' };
                            window.dispatchEvent(new CustomEvent('stock-in-added', { detail: data }));
                        } else {
                            this.addToast(data.message || 'Gagal menambah stok', 'error');
                        }
                    } catch (err) {
                        this.addToast('Terjadi kesalahan koneksi', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitQuickMaterial() {
                    if (!this.materialForm.name || !this.materialForm.cost_per_unit) return;
                    this.isSubmitting = true;
                    try {
                        const res = await fetch('{{ route('dashboard.quick-material') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.materialForm)
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.addToast(data.message, 'success');
                            this.showMaterialModal = false;
                            this.materialForm = { name: '', cost_per_unit: '', unit_id: '', category_id: '', sku: '' };
                            window.dispatchEvent(new CustomEvent('material-added', { detail: data.material }));
                        } else {
                            this.addToast(data.message || 'Gagal menambah bahan baku', 'error');
                        }
                    } catch (err) {
                        this.addToast('Terjadi kesalahan koneksi', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }">

                <!-- Floating Toasts Container -->
                <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-2.5 max-w-sm pointer-events-none">
                    <template x-for="t in toastList" :key="t.id">
                        <div x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-90"
                            :class="t.type === 'error' ? 'bg-rose-950/90 border-rose-500/40 text-rose-200' :
                                'bg-slate-900/95 border-emerald-500/40 text-emerald-200'"
                            class="p-4 rounded-2xl border shadow-2xl backdrop-blur-xl pointer-events-auto flex items-center gap-3 text-xs font-semibold">
                            <i :data-lucide="t.type === 'error' ? 'alert-triangle' : 'check-circle-2'"
                                :class="t.type === 'error' ? 'text-rose-400' : 'text-emerald-400'"
                                class="w-5 h-5 shrink-0"></i>
                            <span x-text="t.msg" class="flex-1"></span>
                        </div>
                    </template>
                </div>

                <!-- Modal 1: Quick Expense -->
                <div x-show="showExpenseModal" x-transition
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
                    style="display: none;">
                    <div class="glass-card p-6 rounded-3xl w-full max-w-md border border-slate-800 shadow-2xl space-y-4"
                        @click.outside="showExpenseModal = false">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-xl bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                    <i data-lucide="receipt" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white">Catat Pengeluaran Cepat</h3>
                                    <p class="text-[11px] text-slate-400">Jurnal otomatis tanpa pindah menu</p>
                                </div>
                            </div>
                            <button type="button" @click="showExpenseModal = false"
                                class="text-slate-400 hover:text-white">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickExpense" class="space-y-3.5 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Nama / Keterangan Biaya
                                    *</label>
                                <input type="text" x-model="expenseForm.name" required
                                    placeholder="Contoh: Gas Elpiji 3kg, Plastik Kresek"
                                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-rose-500 rounded-xl text-white">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Nominal (Rp) *</label>
                                    <input type="number" x-model.number="expenseForm.amount" required min="100"
                                        placeholder="25000"
                                        class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-rose-500 rounded-xl text-white font-mono">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Metode Bayar</label>
                                    <select x-model="expenseForm.payment_method"
                                        class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                                        <option value="cash">Kas Tunai (Laci)</option>
                                        <option value="bank">Transfer Bank</option>
                                        <option value="qris">QRIS / e-Wallet</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Kategori Biaya</label>
                                <select x-model="expenseForm.category"
                                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                                    <option value="Operasional Toko">Operasional Toko</option>
                                    <option value="Bahan Habis Pakai">Bahan Habis Pakai (Plastik/Kemasan)</option>
                                    <option value="Listrik, Air & Gas">Listrik, Air & Gas</option>
                                    <option value="Transportasi & Logistik">Transportasi & Logistik</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="showExpenseModal = false"
                                    class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold">
                                    Batal
                                </button>
                                <button type="submit" :disabled="isSubmitting"
                                    class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold flex items-center gap-1.5 shadow-lg shadow-rose-500/20">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Pengeluaran'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal 2: Quick Instant Stock-In -->
                <div x-show="showStockInModal" x-transition
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
                    style="display: none;">
                    <div class="glass-card p-6 rounded-3xl w-full max-w-md border border-slate-800 shadow-2xl space-y-4"
                        @click.outside="showStockInModal = false">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white">Beli Stok Masuk Cepat</h3>
                                    <p class="text-[11px] text-slate-400">1-Klik tambah persediaan & valuasi aset</p>
                                </div>
                            </div>
                            <button type="button" @click="showStockInModal = false"
                                class="text-slate-400 hover:text-white">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickStockIn" class="space-y-3.5 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Bahan Baku / Produk *</label>
                                <select x-model="stockInForm.material_id" required
                                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                                    <option value="">-- Pilih Bahan Baku --</option>
                                    @foreach (\App\Models\Material::where('business_id', $activeBiz?->id)->orderBy('name')->get() as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }} (HPP: Rp
                                            {{ number_format((float) $m->cost_per_unit, 0, ',', '.') }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Jumlah Masuk *</label>
                                    <input type="number" x-model.number="stockInForm.quantity" required
                                        min="0.01" step="any" placeholder="10"
                                        class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Harga Beli / Satuan (Rp)
                                        *</label>
                                    <input type="number" x-model.number="stockInForm.unit_cost" required
                                        min="0" placeholder="15000"
                                        class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Nama Pemasok / Toko Beli</label>
                                <input type="text" x-model="stockInForm.supplier_name"
                                    placeholder="Contoh: Pasar Induk, Toko Bahan Kue Maju"
                                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="showStockInModal = false"
                                    class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold">
                                    Batal
                                </button>
                                <button type="submit" :disabled="isSubmitting"
                                    class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold flex items-center gap-1.5 shadow-lg shadow-emerald-500/20">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Tambah Stok Masuk'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal 3: Quick Create Material -->
                <div x-show="showMaterialModal" x-transition
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
                    style="display: none;">
                    <div class="glass-card p-6 rounded-3xl w-full max-w-md border border-slate-800 shadow-2xl space-y-4"
                        @click.outside="showMaterialModal = false">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    <i data-lucide="boxes" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white">Tambah Bahan Baku Cepat</h3>
                                    <p class="text-[11px] text-slate-400">Daftarkan bahan baku baru tanpa pindah layar
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="showMaterialModal = false"
                                class="text-slate-400 hover:text-white">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickMaterial" class="space-y-3.5 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Nama Bahan Baku *</label>
                                <input type="text" x-model="materialForm.name" required
                                    placeholder="Contoh: Tepung Terigu Segitiga Biru"
                                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-white">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Harga Beli Dasar (Rp)
                                        *</label>
                                    <input type="number" x-model.number="materialForm.cost_per_unit" required
                                        min="0" placeholder="12000"
                                        class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-white font-mono">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Satuan Ukur</label>
                                    <select x-model="materialForm.unit_id"
                                        class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                                        <option value="">Pilih Satuan</option>
                                        @foreach (\App\Models\Unit::where('business_id', $activeBiz?->id)->orWhereNull('business_id')->orderBy('name')->get() as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}
                                                ({{ $u->symbol }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="showMaterialModal = false"
                                    class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold">
                                    Batal
                                </button>
                                <button type="submit" :disabled="isSubmitting"
                                    class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold flex items-center gap-1.5 shadow-lg shadow-blue-500/20">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    <span x-text="isSubmitting ? 'Menyimpan...' : 'Tambah Bahan'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal 4: Mobile Action Sheet Bottom Modal -->
                <div x-show="showMobileActionSheet" x-transition.opacity
                    class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/80 backdrop-blur-sm lg:hidden"
                    style="display: none;">
                    <div class="glass-card p-6 rounded-t-3xl w-full border-t border-slate-700 shadow-2xl space-y-4 max-h-[85vh] overflow-y-auto"
                        @click.outside="showMobileActionSheet = false">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                                    <i data-lucide="zap" class="w-4 h-4"></i>
                                </div>
                                <h3 class="text-sm font-bold text-white">Aksi Cepat Instan</h3>
                            </div>
                            <button type="button" @click="showMobileActionSheet = false"
                                class="text-slate-400 hover:text-white p-1">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <button type="button" @click="showMobileActionSheet = false; showExpenseModal = true"
                                class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-rose-500/40 text-left space-y-2 transition active:scale-95">
                                <div
                                    class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                                    <i data-lucide="receipt" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white">Catat Beban</div>
                                    <div class="text-[10px] text-slate-400">Biaya operasional</div>
                                </div>
                            </button>

                            <button type="button" @click="showMobileActionSheet = false; showStockInModal = true"
                                class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/40 text-left space-y-2 transition active:scale-95">
                                <div
                                    class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white">Beli Stok</div>
                                    <div class="text-[10px] text-slate-400">Tambah persediaan</div>
                                </div>
                            </button>

                            <button type="button" @click="showMobileActionSheet = false; showMaterialModal = true"
                                class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-blue-500/40 text-left space-y-2 transition active:scale-95">
                                <div
                                    class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                                    <i data-lucide="boxes" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white">Bahan Baku</div>
                                    <div class="text-[10px] text-slate-400">Master bahan resep</div>
                                </div>
                            </button>

                            <a href="{{ route('calculator.index') }}"
                                class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/40 text-left space-y-2 transition block active:scale-95">
                                <div
                                    class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white">Hitung HPP</div>
                                    <div class="text-[10px] text-slate-400">3-Pilar harga jual</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Mobile Bottom App Bar (Sticky at Bottom for Mobile Devices) -->
            <div
                class="fixed inset-x-0 bottom-0 z-40 bg-slate-950/95 backdrop-blur-xl border-t border-slate-800/90 lg:hidden px-4 py-2 shadow-2xl">
                <div class="flex items-center justify-around">
                    <!-- 1. Home / Dashboard -->
                    <a href="{{ route('dashboard') }}"
                        class="flex flex-col items-center gap-0.5 py-1 px-2 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span class="text-[10px]">Home</span>
                    </a>

                    <!-- 2. Kasir POS -->
                    <a href="{{ route('pos.terminal') }}"
                        class="flex flex-col items-center gap-0.5 py-1 px-2 rounded-xl transition {{ request()->routeIs('pos.terminal') ? 'text-teal-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="calculator" class="w-5 h-5"></i>
                        <span class="text-[10px]">Kasir</span>
                    </a>

                    <!-- 3. Glowing Quick Action Trigger (Center Thumb Zone) -->
                    <button type="button" @click="$dispatch('open-mobile-actions')"
                        class="w-12 h-12 -mt-6 rounded-2xl bg-gradient-to-tr from-emerald-500 via-teal-400 to-emerald-400 text-slate-950 flex items-center justify-center shadow-lg shadow-emerald-500/40 ring-4 ring-slate-950 font-black hover:scale-105 active:scale-95 transition-all">
                        <i data-lucide="zap" class="w-6 h-6"></i>
                    </button>

                    <!-- 4. Katalog Produk & Stok -->
                    <a href="{{ route('products.index') }}"
                        class="flex flex-col items-center gap-0.5 py-1 px-2 rounded-xl transition {{ request()->routeIs('products.*') || request()->routeIs('inventory.*') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="package" class="w-5 h-5"></i>
                        <span class="text-[10px]">Produk</span>
                    </a>

                    <!-- 5. Menu Drawer Trigger -->
                    <button type="button" @click="sidebarOpen = true"
                        class="flex flex-col items-center gap-0.5 py-1 px-2 rounded-xl text-slate-400 hover:text-slate-200 transition">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                        <span class="text-[10px]">Menu</span>
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <footer
                class="px-6 lg:px-10 py-5 border-t border-slate-900 text-slate-400 text-xs flex flex-col sm:flex-row items-center justify-between gap-2 mb-16 lg:mb-0">
                <div>&copy; {{ date('Y') }} Cooca UMKM (cooca.id). Business Operating System.</div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/api/v1/docs') }}" target="_blank"
                        class="hover:text-emerald-400 transition-colors">API Docs</a>
                    <span>•</span>
                    <a href="{{ route('settings.index') }}" class="hover:text-emerald-400 transition-colors">20
                        Template Bisnis</a>
                </div>
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
        window.coocaToast = function(msg, type = 'success') {
            window.dispatchEvent(new CustomEvent('cooca-toast', {
                detail: {
                    message: msg,
                    type: type
                }
            }));
        };
    </script>

    <!-- Coming Soon Modal -->
    <div x-show="comingSoonOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[200] flex items-center justify-center p-4"
         style="display: none;"
         @click.self="comingSoonOpen = false"
         @keydown.escape.window="comingSoonOpen = false">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

        <!-- Modal Panel -->
        <div x-show="comingSoonOpen"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             class="relative w-full max-w-md mx-auto rounded-3xl overflow-hidden shadow-2xl"
             style="display: none;">

            <!-- Gradient top strip -->
            <div class="h-1.5 w-full"
                 :class="{
                     'bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-600': comingSoonFeature.color === 'purple',
                     'bg-gradient-to-r from-amber-400 via-orange-400 to-amber-500':  comingSoonFeature.color === 'amber',
                     'bg-gradient-to-r from-emerald-400 via-teal-400 to-emerald-500': comingSoonFeature.color === 'emerald'
                 }"></div>

            <div class="bg-slate-950 border border-slate-800 border-t-0 rounded-b-3xl p-8 space-y-6">

                <!-- Close button -->
                <button @click="comingSoonOpen = false" class="absolute top-5 right-5 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>

                <!-- Icon + Title -->
                <div class="flex flex-col items-center text-center space-y-3">
                    <!-- Animated icon ring -->
                    <div class="relative">
                        <div class="absolute inset-0 rounded-full animate-ping opacity-20"
                             :class="{
                                 'bg-purple-500': comingSoonFeature.color === 'purple',
                                 'bg-amber-400':  comingSoonFeature.color === 'amber',
                                 'bg-emerald-500': comingSoonFeature.color === 'emerald'
                             }"></div>
                        <div class="relative w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg"
                             :class="{
                                 'bg-purple-500/20 border border-purple-500/40 shadow-purple-500/20': comingSoonFeature.color === 'purple',
                                 'bg-amber-500/20 border border-amber-500/40 shadow-amber-500/20':   comingSoonFeature.color === 'amber',
                                 'bg-emerald-500/20 border border-emerald-500/40 shadow-emerald-500/20': comingSoonFeature.color === 'emerald'
                             }">
                            <i :data-lucide="comingSoonFeature.icon"
                               class="w-7 h-7"
                               :class="{
                                   'text-purple-400': comingSoonFeature.color === 'purple',
                                   'text-amber-400':  comingSoonFeature.color === 'amber',
                                   'text-emerald-400': comingSoonFeature.color === 'emerald'
                               }"
                               x-init="$watch('comingSoonOpen', v => { if(v) { $nextTick(() => lucide.createIcons()); } })"></i>
                        </div>
                    </div>

                    <!-- Badge -->
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"
                          :class="{
                              'bg-purple-500/20 text-purple-300 border border-purple-500/30': comingSoonFeature.color === 'purple',
                              'bg-amber-500/20 text-amber-300 border border-amber-500/30':   comingSoonFeature.color === 'amber',
                              'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30': comingSoonFeature.color === 'emerald'
                          }">
                        🚀 Segera Hadir
                    </span>

                    <h2 class="text-xl font-black text-white" x-text="comingSoonFeature.title"></h2>
                    <p class="text-sm text-slate-400 leading-relaxed" x-text="comingSoonFeature.desc"></p>
                </div>

                <!-- Countdown Timer -->
                <div x-data="coocaCountdown()" x-init="start()" class="space-y-3">
                    <p class="text-center text-[11px] text-slate-500 font-medium uppercase tracking-wider">Hitung Mundur Peluncuran</p>
                    <div class="grid grid-cols-4 gap-2">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full py-3 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                                <span class="text-2xl font-black font-mono text-white" x-text="String(days).padStart(2,'0')">00</span>
                            </div>
                            <span class="text-[10px] text-slate-500 font-semibold uppercase">Hari</span>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full py-3 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                                <span class="text-2xl font-black font-mono text-white" x-text="String(hours).padStart(2,'0')">00</span>
                            </div>
                            <span class="text-[10px] text-slate-500 font-semibold uppercase">Jam</span>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full py-3 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                                <span class="text-2xl font-black font-mono text-white" x-text="String(minutes).padStart(2,'0')">00</span>
                            </div>
                            <span class="text-[10px] text-slate-500 font-semibold uppercase">Menit</span>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full py-3 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                                <span class="text-2xl font-black font-mono text-white" x-text="String(seconds).padStart(2,'0')">00</span>
                            </div>
                            <span class="text-[10px] text-slate-500 font-semibold uppercase">Detik</span>
                        </div>
                    </div>
                    <!-- Progress bar -->
                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000 bg-gradient-to-r from-emerald-400 to-teal-400"
                             :style="'width:' + progress + '%'"
                                ></div>
                    </div>
                    <p class="text-center text-[10px] text-slate-600" x-text="launchDate"></p>
                </div>

                <!-- CTA -->
                <div class="flex flex-col gap-2">
                    <a href="{{ route('billing.limits') }}"
                       class="flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 text-sm font-black shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02] active:scale-95">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        <span>Lihat Paket & Kuota Saya</span>
                    </a>
                    <button @click="comingSoonOpen = false"
                            class="px-5 py-2.5 rounded-2xl text-slate-400 hover:text-white text-sm font-semibold transition hover:bg-slate-800">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Coming Soon: intercept ai_token checkout links globally -->
    <script>
        (function() {
            // Countdown component
            window.coocaCountdown = function() {
                // Launch date: 1 month from now, stored in localStorage so it's stable per browser
                const KEY = 'cooca_coming_soon_launch';
                let launch = localStorage.getItem(KEY);
                if (!launch) {
                    const d = new Date();
                    d.setMonth(d.getMonth() + 1);
                    launch = d.toISOString();
                    localStorage.setItem(KEY, launch);
                }
                const launchTime = new Date(launch).getTime();
                const totalDuration = launchTime - (launchTime - 30 * 24 * 60 * 60 * 1000); // 30 days in ms

                return {
                    days: 0, hours: 0, minutes: 0, seconds: 0,
                    progress: 0,
                    launchDate: '',
                    _timer: null,
                    start() {
                        const ldate = new Date(launchTime);
                        this.launchDate = 'Target peluncuran: ' + ldate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                        this.tick();
                        this._timer = setInterval(() => this.tick(), 1000);
                    },
                    tick() {
                        const now = Date.now();
                        const diff = Math.max(0, launchTime - now);
                        this.days    = Math.floor(diff / 86400000);
                        this.hours   = Math.floor((diff % 86400000) / 3600000);
                        this.minutes = Math.floor((diff % 3600000)  / 60000);
                        this.seconds = Math.floor((diff % 60000)    / 1000);
                        const elapsed = totalDuration - diff;
                        this.progress = Math.min(100, Math.round((elapsed / totalDuration) * 100));
                    }
                };
            };

            // Intercept all anchor clicks that go to ai_token checkout, pos/ai, or community
            document.addEventListener('click', function(e) {
                const a = e.target.closest('a');
                if (!a) return;
                const href = a.getAttribute('href') || '';
                const fullHref = a.href || '';

                // Match /billing/checkout?type=ai_token
                if (fullHref.includes('billing/checkout') && fullHref.includes('ai_token')) {
                    e.preventDefault();
                    window.dispatchEvent(new CustomEvent('cooca-coming-soon', {
                        detail: {
                            title: 'Top Up Token AI',
                            icon: 'bot',
                            desc: 'Fitur pembelian token AI untuk mengaktifkan AI Assistant, analisis penjualan, dan prediksi tren kasir POS. Segera tersedia!',
                            color: 'amber'
                        }
                    }));
                    return;
                }

                // Match /pos/ai
                if (fullHref.includes('/pos/ai') || href.includes('/pos/ai')) {
                    e.preventDefault();
                    window.dispatchEvent(new CustomEvent('cooca-coming-soon', {
                        detail: {
                            title: 'AI Assistant',
                            icon: 'bot',
                            desc: 'Fitur AI Cockpit untuk analisis penjualan, prediksi tren, dan asisten pintar kasir POS berbasis Gemini AI.',
                            color: 'purple'
                        }
                    }));
                    return;
                }

                // Match /community
                if (fullHref.includes('/community') || href.includes('/community')) {
                    e.preventDefault();
                    window.dispatchEvent(new CustomEvent('cooca-coming-soon', {
                        detail: {
                            title: 'Komunitas Owner',
                            icon: 'users',
                            desc: 'Forum diskusi eksklusif khusus para owner UMKM Cooca — berbagi tips, trik, dan strategi bisnis bersama.',
                            color: 'amber'
                        }
                    }));
                    return;
                }
            }, true);
        })();
    </script>

    <!-- Guided Product Tour Engine -->
    <script src="{{ asset('js/onboarding/tour-config.js') }}"></script>
    <script src="{{ asset('js/onboarding/product-tour.js') }}"></script>

    <!-- AppAlert Session Flash Notifications -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                AppAlert.success(@json(session('success')));
            @endif
            @if (session('error'))
                AppAlert.error(@json(session('error')));
            @endif
            @if (session('warning'))
                AppAlert.warning(@json(session('warning')));
            @endif
            @if (session('info'))
                AppAlert.info(@json(session('info')));
            @endif
        });
    </script>

    @stack('scripts')
</body>

</html>
