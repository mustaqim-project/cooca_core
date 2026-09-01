<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Cooca Core' }} — cooca.id</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
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

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
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
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased" 
      x-data="{ 
          sidebarOpen: false,
          init() {
              window.addEventListener('tour-open-sidebar', () => { this.sidebarOpen = true; });
              window.addEventListener('tour-close-sidebar', () => { this.sidebarOpen = false; });
          }
      }">
    <div class="min-h-full flex flex-col lg:flex-row">
        
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/80 z-40 lg:hidden" 
             @click="sidebarOpen = false" 
             style="display: none;"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 h-screen max-h-screen glass-nav flex flex-col justify-between transition-transform duration-300 ease-in-out">
            
            <!-- Brand Logo -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800 shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        <i data-lucide="boxes" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <div class="font-extrabold text-lg text-white tracking-tight leading-none">Cooca Core</div>
                        <div class="text-[10px] font-semibold text-emerald-400 uppercase tracking-wider mt-1">cooca.id • Business OS</div>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800">
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
            @if($activeBiz)
            <div class="px-4 py-3 border-b border-slate-800/80 shrink-0">
                <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        @if($activeBiz->logo_url)
                            <div class="w-8 h-8 rounded-lg bg-white/5 border border-slate-700 flex items-center justify-center shrink-0 overflow-hidden p-0.5">
                                <img src="{{ $activeBiz->logo_url }}" alt="{{ $activeBiz->name }}" class="w-full h-full object-contain">
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center shrink-0">
                                <i data-lucide="building-2" class="w-4 h-4 text-emerald-400"></i>
                            </div>
                        @endif
                        <div class="overflow-hidden">
                            <div class="text-xs text-slate-400 font-medium">Bisnis Aktif</div>
                            <div class="text-sm font-bold text-white truncate">{{ $activeBiz->name }}</div>
                        </div>
                    </div>
                    <a href="{{ route('businesses.select') }}" title="Ganti Bisnis" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-emerald-400 transition-colors">
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

                $isSalesRoute = request()->routeIs('pos.*') || request()->routeIs('invoices.*') || request()->routeIs('sales.*') || request()->routeIs('customers.*') || request()->routeIs('crm.*');
                $isInventoryRoute = request()->routeIs('products.*') || request()->routeIs('materials.*') || request()->routeIs('suppliers.*') || request()->routeIs('labor-machines.*') || request()->routeIs('inventory.*') || request()->routeIs('purchase-orders.*');
                $isFinanceRoute = request()->routeIs('finance.*') || request()->routeIs('profitability.*') || request()->routeIs('simulator.*') || request()->routeIs('reports.*');
            @endphp

            <nav class="flex-1 overflow-y-auto px-3.5 py-3 space-y-3 min-h-0 overscroll-contain text-xs"
                 x-data="{
                     salesOpen: {{ $isSalesRoute ? 'true' : 'false' }},
                     inventoryOpen: {{ $isInventoryRoute ? 'true' : 'false' }},
                     financeOpen: {{ $isFinanceRoute ? 'true' : 'false' }}
                 }">
                
                <!-- 1. RINGKASAN & INTI -->
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" id="tour-nav-dashboard"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 shadow-sm' : 'text-slate-300 hover:bg-slate-900/80 hover:text-white' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 text-emerald-400"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('calculator.index') }}" id="tour-nav-calculator"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('calculator.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 shadow-sm' : 'text-slate-300 hover:bg-slate-900/80 hover:text-white' }}">
                        <i data-lucide="sparkles" class="w-4 h-4 text-emerald-400"></i>
                        <span>Kalkulator HPP</span>
                        <span class="ml-auto text-[9px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold">3-Pilar</span>
                    </a>

                    <a href="{{ route('pos.ai.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('pos.ai.*') ? 'bg-purple-500/15 text-purple-300 border border-purple-500/30 shadow-sm' : 'text-slate-300 hover:bg-slate-900/80 hover:text-white' }}">
                        <i data-lucide="bot" class="w-4 h-4 text-purple-400"></i>
                        <span>AI Assistant</span>
                        <span class="ml-auto text-[9px] px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 font-black border border-purple-500/30">AI</span>
                    </a>
                </div>

                <!-- 2. KASIR & PENJUALAN (COLLAPSIBLE) -->
                <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 overflow-hidden">
                    <button type="button" @click="salesOpen = !salesOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-left font-semibold text-slate-300 hover:text-white hover:bg-slate-900/50 transition">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="shopping-cart" class="w-4 h-4 text-teal-400"></i>
                            <span class="text-xs">Kasir & Penjualan</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="salesOpen ? 'rotate-180 text-teal-400' : ''"></i>
                    </button>

                    <div x-show="salesOpen" x-transition.opacity class="px-2 pb-2 space-y-0.5 pt-1 border-t border-slate-900">
                        <a href="{{ route('pos.terminal') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('pos.terminal') ? 'bg-teal-500/15 text-teal-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="calculator" class="w-3.5 h-3.5 text-teal-400"></i>
                            <span>Terminal Kasir POS</span>
                        </a>

                        <a href="{{ route('invoices.index') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('invoices.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>Faktur & Piutang</span>
                        </a>

                        <a href="{{ route('pos.orders.index') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('pos.orders.*') || request()->routeIs('pos.shifts.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Riwayat Transaksi & Shift</span>
                        </a>

                        <a href="{{ route('customers.index') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('customers.*') || request()->routeIs('crm.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-400"></i>
                            <span>Pelanggan & CRM</span>
                        </a>

                        <a href="{{ route('sales.orders.index') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('sales.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="check-square" class="w-3.5 h-3.5 text-cyan-400"></i>
                            <span>Pesanan & Penawaran</span>
                        </a>
                    </div>
                </div>

                <!-- 3. PRODUK & GUDANG STOK (COLLAPSIBLE) -->
                <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 overflow-hidden">
                    <button type="button" @click="inventoryOpen = !inventoryOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-left font-semibold text-slate-300 hover:text-white hover:bg-slate-900/50 transition">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="package" class="w-4 h-4 text-emerald-400"></i>
                            <span class="text-xs">Produk & Inventori</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="inventoryOpen ? 'rotate-180 text-emerald-400' : ''"></i>
                    </button>

                    <div x-show="inventoryOpen" x-transition.opacity class="px-2 pb-2 space-y-0.5 pt-1 border-t border-slate-900">
                        <a href="{{ route('products.index') }}" id="tour-nav-products"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('products.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="package" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>Katalog Produk & Resep</span>
                        </a>

                        <a href="{{ route('materials.index') }}" id="tour-nav-materials"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('materials.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="boxes" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Bahan Baku & Harga</span>
                        </a>

                        <a href="{{ route('inventory.stocks') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('inventory.*') ? 'bg-cyan-500/15 text-cyan-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="warehouse" class="w-3.5 h-3.5 text-cyan-400"></i>
                            <span>Stok Real-Time & Mutasi</span>
                        </a>

                        <a href="{{ route('purchase-orders.index') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('purchase-orders.*') || request()->routeIs('suppliers.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="truck" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>PO & Pemasok</span>
                        </a>

                        <a href="{{ route('labor-machines.index') }}" id="tour-nav-labor-machines"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('labor-machines.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="users-2" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Upah Kerja & Mesin</span>
                        </a>
                    </div>
                </div>

                <!-- 4. KEUANGAN & LAPORAN (COLLAPSIBLE) -->
                <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 overflow-hidden">
                    <button type="button" @click="financeOpen = !financeOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-left font-semibold text-slate-300 hover:text-white hover:bg-slate-900/50 transition">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="trending-up" class="w-4 h-4 text-amber-400"></i>
                            <span class="text-xs">Keuangan & Analitik</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="financeOpen ? 'rotate-180 text-amber-400' : ''"></i>
                    </button>

                    <div x-show="financeOpen" x-transition.opacity class="px-2 pb-2 space-y-0.5 pt-1 border-t border-slate-900">
                        <a href="{{ route('reports.index') }}" id="tour-nav-reports"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-amber-500/15 text-amber-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="bar-chart-3" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Laporan & Analitik</span>
                        </a>

                        <a href="{{ route('profitability.index') }}" id="tour-nav-profitability"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('profitability.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="target" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>BEP & Profitabilitas</span>
                        </a>

                        <a href="{{ route('finance.expenses.index') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('finance.expenses.*') ? 'bg-rose-500/15 text-rose-300 font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="wallet" class="w-3.5 h-3.5 text-rose-400"></i>
                            <span>Beban Operasional</span>
                        </a>

                        <a href="{{ route('finance.journals.index') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('finance.journals.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="book-open" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Jurnal Akuntansi Otomatis</span>
                        </a>

                        <a href="{{ route('simulator.index') }}" id="tour-nav-simulator"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition-all {{ request()->routeIs('simulator.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <i data-lucide="sliders" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Simulasi What-If</span>
                        </a>
                    </div>
                </div>

                <!-- 5. PENGATURAN & LISENSI -->
                <div class="space-y-1 pt-1">
                    <a href="{{ route('settings.index') }}" id="tour-nav-settings"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition-all {{ request()->routeIs('settings.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i data-lucide="settings" class="w-4 h-4 text-slate-400"></i>
                        <span>Pengaturan Toko & Tim</span>
                    </a>

                    <a href="{{ route('billing.limits') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition-all {{ request()->routeIs('billing.*') ? 'bg-purple-500/15 text-purple-300 border border-purple-500/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i data-lucide="sparkles" class="w-4 h-4 text-purple-400"></i>
                        <span>Paket & Kuota</span>
                        <span class="ml-auto text-[9px] px-2 py-0.5 rounded-full {{ $isCorePlan ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }} font-black uppercase">
                            {{ $isCorePlan ? 'Core' : 'Free' }}
                        </span>
                    </a>

                    @if(\App\Support\Context::isOwner())
                    <a href="{{ route('feedback.bugs.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition-all {{ request()->routeIs('feedback.*') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i data-lucide="life-buoy" class="w-4 h-4 text-cyan-400"></i>
                        <span>Dukungan Produk</span>
                    </a>
                    @endif
                </div>

            </nav>

            <!-- User Profile Bottom Bar (Sticky at Bottom) -->
            <div class="p-3 border-t border-slate-800/80 space-y-2 shrink-0 bg-slate-950/90">
                <form method="POST" action="{{ route('onboarding.restart') }}">
                    @csrf
                    <button type="submit" class="w-full px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-[11px] font-semibold flex items-center justify-center gap-1.5 transition-colors">
                        <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                        <span>Mulai Ulang Panduan Tour</span>
                    </button>
                </form>

                <div class="flex items-center justify-between">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 overflow-hidden group flex-1 mr-2 p-1 rounded-xl hover:bg-slate-900 transition-colors">
                        <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-emerald-400 shrink-0 group-hover:border-emerald-500/50">
                            {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                        </div>
                        <div class="overflow-hidden text-left">
                            <div class="text-xs font-semibold text-white truncate group-hover:text-emerald-400 transition-colors">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-[10px] text-slate-400 truncate flex items-center gap-1">
                                <i data-lucide="key" class="w-3 h-3 text-emerald-400"></i>
                                <span>Profil & Sandi</span>
                            </div>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 hover:bg-red-500/10 text-slate-400 hover:text-red-400 rounded-lg transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 lg:pl-72 flex flex-col min-h-screen">
            
            <!-- Topbar Header -->
            <header class="h-20 glass-header sticky top-0 z-30 flex items-center justify-between px-6 lg:px-10">
                <div class="flex items-center gap-4">
                    <button id="tour-mobile-menu-btn" @click="sidebarOpen = true; window.dispatchEvent(new CustomEvent('sidebar-opened'))" class="lg:hidden p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold text-white">{{ $headerTitle ?? 'Cooca Core' }}</h1>
                        <p class="text-xs text-slate-400 hidden sm:block">{{ $headerSubtitle ?? 'Sistem Perhitungan HPP & Manajemen Komersial Terintegrasi' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    @if($activeBiz && $navUsage)
                    <!-- Plan Tracking & Subscription Status Popover -->
                    <div class="relative" x-data="{ planDropdownOpen: false }" @click.outside="planDropdownOpen = false">
                        @if($isCorePlan)
                            <!-- Core Plan Active Pill -->
                            <button @click="planDropdownOpen = !planDropdownOpen" type="button"
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-950/40 hover:bg-emerald-900/40 border border-emerald-500/40 text-xs text-white transition shadow-sm group">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400"></span>
                                <span class="font-black text-emerald-300">Cooca Core</span>
                                <span class="hidden md:inline text-[10px] text-emerald-400/80 font-mono">({{ number_format(($navUsage['ai_tokens']['remaining'] ?? 0) / 1000000, 1) }}M AI)</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-emerald-400 transition-transform duration-200" :class="planDropdownOpen ? 'rotate-180' : ''"></i>
                            </button>
                        @else
                            <!-- Free Plan Pill with Upgrade CTA -->
                            <div class="flex items-center gap-1.5">
                                <button @click="planDropdownOpen = !planDropdownOpen" type="button"
                                        class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900/90 hover:bg-slate-800 border border-amber-500/40 text-xs text-slate-200 transition shadow-sm group">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                    <span class="font-bold text-amber-300">Free Plan</span>
                                    <span class="hidden sm:inline text-[10px] text-slate-400 font-mono">({{ $navUsage['products']['used'] ?? 0 }}/50 Prod)</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="planDropdownOpen ? 'rotate-180' : ''"></i>
                                </button>

                                <a href="{{ route('billing.checkout') }}"
                                   class="hidden sm:flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-400 hover:to-indigo-400 text-white text-[11px] font-black shadow-md shadow-purple-500/20 transition">
                                    <i data-lucide="zap" class="w-3 h-3 text-amber-300"></i>
                                    <span>Upgrade</span>
                                </a>
                            </div>
                        @endif

                        <!-- Floating Popover Dropdown Card -->
                        <div x-show="planDropdownOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute right-0 mt-2 w-80 rounded-3xl bg-slate-950 border border-slate-800 p-5 shadow-2xl z-50 space-y-4 text-left"
                             style="display: none;">
                            
                            <!-- Header Status -->
                            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                <div>
                                    <div class="text-xs font-black text-white flex items-center gap-1.5">
                                        @if($isCorePlan)
                                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                                            <span>Cooca Core License</span>
                                        @else
                                            <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                                            <span>Paket Free (Gratis)</span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $isCorePlan ? 'Aktif s/d ' . ($navUsage['ends_at'] ?? '-') : 'Batas sumber daya UMKM starter' }}
                                    </p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase {{ $isCorePlan ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                                    {{ $navUsage['status'] ?? 'Active' }}
                                </span>
                            </div>

                            @if(!$isCorePlan)
                            <!-- Usage Quota Progress Bars for Free Plan -->
                            <div class="space-y-3 text-xs">
                                <!-- Products -->
                                <div class="space-y-1">
                                    <div class="flex justify-between text-[11px]">
                                        <span class="text-slate-400">Katalog Produk:</span>
                                        <span class="font-mono font-bold {{ ($navUsage['products']['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">
                                            {{ $navUsage['products']['used'] ?? 0 }} / {{ $navUsage['products']['limit'] ?? 50 }}
                                        </span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ ($navUsage['products']['percent'] ?? 0) >= 90 ? 'bg-rose-500' : (($navUsage['products']['percent'] ?? 0) >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                             style="width: {{ $navUsage['products']['percent'] ?? 0 }}%"></div>
                                    </div>
                                </div>

                                <!-- Recipes -->
                                <div class="space-y-1">
                                    <div class="flex justify-between text-[11px]">
                                        <span class="text-slate-400">Resep / BOM:</span>
                                        <span class="font-mono font-bold {{ ($navUsage['recipes']['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">
                                            {{ $navUsage['recipes']['used'] ?? 0 }} / {{ $navUsage['recipes']['limit'] ?? 20 }}
                                        </span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ ($navUsage['recipes']['percent'] ?? 0) >= 90 ? 'bg-rose-500' : (($navUsage['recipes']['percent'] ?? 0) >= 70 ? 'bg-amber-500' : 'bg-cyan-500') }}"
                                             style="width: {{ $navUsage['recipes']['percent'] ?? 0 }}%"></div>
                                    </div>
                                </div>

                                <!-- Invoices -->
                                <div class="space-y-1">
                                    <div class="flex justify-between text-[11px]">
                                        <span class="text-slate-400">Faktur Bulan Ini:</span>
                                        <span class="font-mono font-bold {{ ($navUsage['invoices_this_month']['is_reached'] ?? false) ? 'text-rose-400' : 'text-white' }}">
                                            {{ $navUsage['invoices_this_month']['used'] ?? 0 }} / {{ $navUsage['invoices_this_month']['limit'] ?? 10 }}
                                        </span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ ($navUsage['invoices_this_month']['percent'] ?? 0) >= 90 ? 'bg-rose-500' : (($navUsage['invoices_this_month']['percent'] ?? 0) >= 70 ? 'bg-amber-500' : 'bg-indigo-500') }}"
                                             style="width: {{ $navUsage['invoices_this_month']['percent'] ?? 0 }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upgrade Teaser Box -->
                            <div class="p-3.5 rounded-2xl bg-purple-950/30 border border-purple-500/30 space-y-2">
                                <div class="text-[11px] font-bold text-white flex items-center gap-1.5">
                                    <i data-lucide="zap" class="w-3.5 h-3.5 text-yellow-300"></i>
                                    <span>Tingkatkan ke Cooca Core</span>
                                </div>
                                <p class="text-[10px] text-slate-400 leading-relaxed">
                                    Buka akses produk tanpa batas & 10 Juta Token AI seharga Rp129.000/bln.
                                </p>
                                <a href="{{ route('billing.checkout', ['cycle' => 'monthly']) }}"
                                   class="block w-full py-2 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-400 hover:to-indigo-400 text-center text-white text-xs font-black shadow-lg shadow-purple-500/20 transition">
                                    Pilih Paket Core
                                </a>
                            </div>
                            @else
                            <!-- Core Active Detail -->
                            <div class="space-y-2.5 text-xs">
                                <div class="flex justify-between py-1 border-b border-slate-900">
                                    <span class="text-slate-400">Katalog & Resep:</span>
                                    <span class="font-bold text-emerald-400">Unlimited (Tanpa Batas)</span>
                                </div>
                                <div class="flex justify-between py-1 border-b border-slate-900">
                                    <span class="text-slate-400">Multi-Gudang & Cabang:</span>
                                    <span class="font-bold text-white">Aktif Penuh</span>
                                </div>
                                <div class="space-y-1 pt-1">
                                    <div class="flex justify-between text-[11px]">
                                        <span class="text-slate-400">Sisa Kuota Token AI:</span>
                                        <span class="font-mono font-bold text-purple-300">
                                            {{ number_format($navUsage['ai_tokens']['remaining'] ?? 0, 0, ',', '.') }} Token
                                        </span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                        <div class="h-full rounded-full bg-purple-500 transition-all duration-500"
                                             style="width: {{ min(100, max(5, 100 - ($navUsage['ai_tokens']['percent'] ?? 0))) }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="pt-1 text-center border-t border-slate-800/80">
                                <a href="{{ route('billing.limits') }}" class="text-[11px] text-emerald-400 hover:underline inline-flex items-center gap-1 font-semibold">
                                    <span>Lihat Kuota & Riwayat Tagihan</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($activeBiz)
                    <div class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-xs text-slate-300">
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
                             class="absolute right-0 mt-2 w-56 rounded-2xl bg-slate-950/95 backdrop-blur-xl border border-slate-800 shadow-2xl p-2 z-50 space-y-1 text-xs" style="display: none;">
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

                    <a href="{{ route('calculator.index') }}" class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center gap-1.5 transition-all shrink-0">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Hitung HPP</span>
                    </a>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 p-6 lg:p-10 space-y-6">
                <!-- Flash Alerts -->
                @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 flex items-center gap-3">
                    <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 text-emerald-400"></i>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
                @endif

                @if(session('error'))
                <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 flex items-center gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-red-400"></i>
                    <div class="text-sm font-medium">{{ session('error') }}</div>
                </div>
                @endif

                @if($errors->any())
                <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 space-y-1">
                    <div class="flex items-center gap-2 font-semibold text-sm">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-400"></i>
                        <span>Terdapat kesalahan input:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 pl-2 text-red-200">
                        @foreach($errors->all() as $err)
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
                             :class="t.type === 'error' ? 'bg-rose-950/90 border-rose-500/40 text-rose-200' : 'bg-slate-900/95 border-emerald-500/40 text-emerald-200'"
                             class="p-4 rounded-2xl border shadow-2xl backdrop-blur-xl pointer-events-auto flex items-center gap-3 text-xs font-semibold">
                            <i :data-lucide="t.type === 'error' ? 'alert-triangle' : 'check-circle-2'" 
                               :class="t.type === 'error' ? 'text-rose-400' : 'text-emerald-400'" class="w-5 h-5 shrink-0"></i>
                            <span x-text="t.msg" class="flex-1"></span>
                        </div>
                    </template>
                </div>

                <!-- Modal 1: Quick Expense -->
                <div x-show="showExpenseModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" style="display: none;">
                    <div class="glass-card p-6 rounded-3xl w-full max-w-md border border-slate-800 shadow-2xl space-y-4" @click.outside="showExpenseModal = false">
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
                            <button type="button" @click="showExpenseModal = false" class="text-slate-400 hover:text-white">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickExpense" class="space-y-3.5 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Nama / Keterangan Biaya *</label>
                                <input type="text" x-model="expenseForm.name" required placeholder="Contoh: Gas Elpiji 3kg, Plastik Kresek"
                                       class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-rose-500 rounded-xl text-white">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Nominal (Rp) *</label>
                                    <input type="number" x-model.number="expenseForm.amount" required min="100" placeholder="25000"
                                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-rose-500 rounded-xl text-white font-mono">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Metode Bayar</label>
                                    <select x-model="expenseForm.payment_method" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                                        <option value="cash">Kas Tunai (Laci)</option>
                                        <option value="bank">Transfer Bank</option>
                                        <option value="qris">QRIS / e-Wallet</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Kategori Biaya</label>
                                <select x-model="expenseForm.category" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                                    <option value="Operasional Toko">Operasional Toko</option>
                                    <option value="Bahan Habis Pakai">Bahan Habis Pakai (Plastik/Kemasan)</option>
                                    <option value="Listrik, Air & Gas">Listrik, Air & Gas</option>
                                    <option value="Transportasi & Logistik">Transportasi & Logistik</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="showExpenseModal = false" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold">
                                    Batal
                                </button>
                                <button type="submit" :disabled="isSubmitting" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold flex items-center gap-1.5 shadow-lg shadow-rose-500/20">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Pengeluaran'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal 2: Quick Instant Stock-In -->
                <div x-show="showStockInModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" style="display: none;">
                    <div class="glass-card p-6 rounded-3xl w-full max-w-md border border-slate-800 shadow-2xl space-y-4" @click.outside="showStockInModal = false">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white">Beli Stok Masuk Cepat</h3>
                                    <p class="text-[11px] text-slate-400">1-Klik tambah persediaan & valuasi aset</p>
                                </div>
                            </div>
                            <button type="button" @click="showStockInModal = false" class="text-slate-400 hover:text-white">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickStockIn" class="space-y-3.5 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Bahan Baku / Produk *</label>
                                <select x-model="stockInForm.material_id" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                                    <option value="">-- Pilih Bahan Baku --</option>
                                    @foreach(\App\Models\Material::where('business_id', $activeBiz?->id)->orderBy('name')->get() as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }} (HPP: Rp {{ number_format((float)$m->cost_per_unit, 0, ',', '.') }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Jumlah Masuk *</label>
                                    <input type="number" x-model.number="stockInForm.quantity" required min="0.01" step="any" placeholder="10"
                                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Harga Beli / Satuan (Rp) *</label>
                                    <input type="number" x-model.number="stockInForm.unit_cost" required min="0" placeholder="15000"
                                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Nama Pemasok / Toko Beli</label>
                                <input type="text" x-model="stockInForm.supplier_name" placeholder="Contoh: Pasar Induk, Toko Bahan Kue Maju"
                                       class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="showStockInModal = false" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold">
                                    Batal
                                </button>
                                <button type="submit" :disabled="isSubmitting" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold flex items-center gap-1.5 shadow-lg shadow-emerald-500/20">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Tambah Stok Masuk'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal 3: Quick Create Material -->
                <div x-show="showMaterialModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" style="display: none;">
                    <div class="glass-card p-6 rounded-3xl w-full max-w-md border border-slate-800 shadow-2xl space-y-4" @click.outside="showMaterialModal = false">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    <i data-lucide="boxes" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white">Tambah Bahan Baku Cepat</h3>
                                    <p class="text-[11px] text-slate-400">Daftarkan bahan baku baru tanpa pindah layar</p>
                                </div>
                            </div>
                            <button type="button" @click="showMaterialModal = false" class="text-slate-400 hover:text-white">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickMaterial" class="space-y-3.5 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Nama Bahan Baku *</label>
                                <input type="text" x-model="materialForm.name" required placeholder="Contoh: Tepung Terigu Segitiga Biru"
                                       class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-white">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Harga Beli Dasar (Rp) *</label>
                                    <input type="number" x-model.number="materialForm.cost_per_unit" required min="0" placeholder="12000"
                                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-white font-mono">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Satuan Ukur</label>
                                    <select x-model="materialForm.unit_id" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                                        <option value="">Pilih Satuan</option>
                                        @foreach(\App\Models\Unit::where('business_id', $activeBiz?->id)->orWhereNull('business_id')->orderBy('name')->get() as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->symbol }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="showMaterialModal = false" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold">
                                    Batal
                                </button>
                                <button type="submit" :disabled="isSubmitting" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold flex items-center gap-1.5 shadow-lg shadow-blue-500/20">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    <span x-text="isSubmitting ? 'Menyimpan...' : 'Tambah Bahan'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal 4: Mobile Action Sheet Bottom Modal -->
                <div x-show="showMobileActionSheet" x-transition.opacity class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/80 backdrop-blur-sm lg:hidden" style="display: none;">
                    <div class="glass-card p-6 rounded-t-3xl w-full border-t border-slate-700 shadow-2xl space-y-4 max-h-[85vh] overflow-y-auto" @click.outside="showMobileActionSheet = false">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                                    <i data-lucide="zap" class="w-4 h-4"></i>
                                </div>
                                <h3 class="text-sm font-bold text-white">Aksi Cepat Instan</h3>
                            </div>
                            <button type="button" @click="showMobileActionSheet = false" class="text-slate-400 hover:text-white p-1">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <button type="button" @click="showMobileActionSheet = false; showExpenseModal = true"
                                    class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-rose-500/40 text-left space-y-2 transition active:scale-95">
                                <div class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                                    <i data-lucide="receipt" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white">Catat Beban</div>
                                    <div class="text-[10px] text-slate-400">Biaya operasional</div>
                                </div>
                            </button>

                            <button type="button" @click="showMobileActionSheet = false; showStockInModal = true"
                                    class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/40 text-left space-y-2 transition active:scale-95">
                                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white">Beli Stok</div>
                                    <div class="text-[10px] text-slate-400">Tambah persediaan</div>
                                </div>
                            </button>

                            <button type="button" @click="showMobileActionSheet = false; showMaterialModal = true"
                                    class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-blue-500/40 text-left space-y-2 transition active:scale-95">
                                <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                                    <i data-lucide="boxes" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white">Bahan Baku</div>
                                    <div class="text-[10px] text-slate-400">Master bahan resep</div>
                                </div>
                            </button>

                            <a href="{{ route('calculator.index') }}"
                               class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/40 text-left space-y-2 transition block active:scale-95">
                                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
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
            <div class="fixed inset-x-0 bottom-0 z-40 bg-slate-950/95 backdrop-blur-xl border-t border-slate-800/90 lg:hidden px-4 py-2 shadow-2xl">
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
            <footer class="px-6 lg:px-10 py-5 border-t border-slate-900 text-slate-400 text-xs flex flex-col sm:flex-row items-center justify-between gap-2 mb-16 lg:mb-0">
                <div>&copy; {{ date('Y') }} Cooca Core (cooca.id). Business Operating System.</div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/api/v1/docs') }}" target="_blank" class="hover:text-emerald-400 transition-colors">API Docs</a>
                    <span>•</span>
                    <a href="{{ route('settings.index') }}" class="hover:text-emerald-400 transition-colors">20 Template Bisnis</a>
                </div>
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
        window.coocaToast = function(msg, type = 'success') {
            window.dispatchEvent(new CustomEvent('cooca-toast', { detail: { message: msg, type: type } }));
        };
    </script>
    
    <!-- Guided Product Tour Engine -->
    <script src="{{ asset('js/onboarding/tour-config.js') }}"></script>
    <script src="{{ asset('js/onboarding/product-tour.js') }}"></script>

    @stack('scripts')
</body>
</html>

