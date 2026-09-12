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
        request()->routeIs('warehouse.*') ||
        request()->routeIs('import.*');
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

    // Granular RBAC Permissions
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
        \App\Support\Context::hasPermission('customers.view') ||
        \App\Support\Context::hasPermission('crm.view');

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

    $canAccessCosting =
        \App\Support\Context::hasPermission('costing.view_margin') ||
        \App\Support\Context::hasPermission('costing.manage') ||
        \App\Support\Context::hasPermission('labor_machines.view') ||
        \App\Support\Context::hasPermission('reports.costing');

    $canAccessFinance =
        \App\Support\Context::hasPermission('accounting.view') ||
        \App\Support\Context::hasPermission('finance.cash_bank') ||
        \App\Support\Context::hasPermission('finance.receivables') ||
        \App\Support\Context::hasPermission('finance.payables') ||
        \App\Support\Context::hasPermission('expenses.view') ||
        \App\Support\Context::hasPermission('expenses.manage');

    $canAccessReports =
        \App\Support\Context::hasPermission('reports.view') ||
        \App\Support\Context::hasPermission('pos.reports');

    $canAccessChannels =
        \App\Support\Context::hasPermission('whatsapp.view');

    $canAccessSettings = \App\Support\Context::hasPermission('settings.view') || \App\Support\Context::isOwner();
    $canAccessRoles = \App\Support\Context::hasPermission('roles.view') || \App\Support\Context::isOwner();
    $canAccessBilling = \App\Support\Context::hasPermission('billing.view') || \App\Support\Context::isOwner();
    $canAccessMasterData =
        \App\Support\Context::hasPermission('master_data.material_categories.view') ||
        \App\Support\Context::hasPermission('master_data.product_categories.view') ||
        \App\Support\Context::hasPermission('master_data.units.view');

    $canAccessSystem =
        $canAccessMasterData ||
        $canAccessSettings ||
        $canAccessRoles ||
        $canAccessBilling ||
        \App\Support\Context::isOwner();
@endphp

{{-- Apple HIG (macOS Sonoma & iOS 18) Sidebar Rail Styles --}}
<style>
/* Calm Apple-style rhythm for the expanded navigation rail. */
.sidebar-nav {
    padding: 1rem 0.75rem !important;
}

.sidebar-nav .sidebar-item {
    min-height: 2.5rem;
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

.sidebar-nav > div {
    margin-bottom: 0.75rem;
}

.sidebar-nav > div > div[x-show="!sidebarCollapsed"] {
    min-height: 1.25rem;
    display: flex;
    align-items: center;
    padding-left: 0.75rem !important;
    padding-right: 0.75rem !important;
    letter-spacing: 0.06em;
}

@media (max-width: 1023px) {
    .app-sidebar {
        width: 336px !important;
    }

    .sidebar-header {
        height: 4rem !important;
        padding-left: 1.25rem !important;
        padding-right: 1.25rem !important;
    }

    .sidebar-tenant-wrapper {
        padding: 1rem 0.875rem !important;
    }
}

@media (min-width: 1024px) {
    .app-sidebar:not(.is-collapsed) {
        width: 272px !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-header {
        height: 4rem !important;
        padding-left: 1.25rem !important;
        padding-right: 1.25rem !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-tenant-wrapper {
        padding: 0.875rem 1rem !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-nav {
        padding: 0.875rem 0.75rem !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-item {
        min-height: 2.5rem !important;
        padding-top: 0.625rem !important;
        padding-bottom: 0.625rem !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-nav > div > div[x-show="!sidebarCollapsed"] {
        margin-top: 0.75rem !important;
        margin-bottom: 0.375rem !important;
    }
}

@media (min-width: 1024px) {
    /* 1. Collapsed Sidebar Container (Clean 76px Icon Rail) */
    .app-sidebar.is-collapsed {
        width: 76px !important;
    }

    /* 2. Brand Logo Header: Perfectly Centered on X=38px Axis */
    .app-sidebar.is-collapsed .sidebar-header {
        padding-left: 0 !important;
        padding-right: 0 !important;
        justify-content: center !important;
    }
    .app-sidebar.is-collapsed .sidebar-header a {
        justify-content: center !important;
        width: 100% !important;
        gap: 0 !important;
    }
    .app-sidebar.is-collapsed .sidebar-header #tour-active-business {
        margin: 0 auto !important;
    }
    .app-sidebar.is-collapsed .sidebar-brand-text {
        display: none !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }

    /* 3. Tenant / Active Business Switcher: Uniform 40x40 Squircle Centered on X=38px */
    .app-sidebar.is-collapsed .sidebar-tenant-wrapper {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        display: flex !important;
        justify-content: center !important;
    }
    .app-sidebar.is-collapsed .sidebar-tenant-card {
        width: 2.5rem !important;      /* 40px */
        height: 2.5rem !important;     /* 40px */
        min-width: 2.5rem !important;
        min-height: 2.5rem !important;
        max-width: 2.5rem !important;
        max-height: 2.5rem !important;
        padding: 0 !important;
        margin: 0 auto !important;
        border-radius: 10px !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        border-color: rgba(60, 60, 67, 0.08) !important;
        box-sizing: border-box !important;
    }
    .dark .app-sidebar.is-collapsed .sidebar-tenant-card {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    .app-sidebar.is-collapsed .sidebar-tenant-inner {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        width: 100% !important;
        height: 100% !important;
        gap: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .app-sidebar.is-collapsed .sidebar-tenant-icon {
        margin: 0 auto !important;
        border: none !important;
        background: transparent !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
    }
    .app-sidebar.is-collapsed .sidebar-tenant-icon img {
        width: 1.5rem !important;
        height: 1.5rem !important;
        border-radius: 6px !important;
        object-fit: contain !important;
    }
    .app-sidebar.is-collapsed .sidebar-tenant-icon svg,
    .app-sidebar.is-collapsed .sidebar-tenant-icon i {
        width: 1.25rem !important;
        height: 1.25rem !important;
        color: #007AFF !important;
    }
    .app-sidebar.is-collapsed .sidebar-tenant-inner > div:not(.sidebar-tenant-icon),
    .app-sidebar.is-collapsed #tour-switch-business {
        display: none !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }

    /* 4. Navigation Container */
    .app-sidebar.is-collapsed .sidebar-nav {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-top: 0.625rem !important;
        padding-bottom: 0.625rem !important;
        overflow-x: hidden !important;
    }

    .app-sidebar.is-collapsed .sidebar-nav > div {
        margin-bottom: 0 !important;
    }

    /* 5. Navigation Items (Links & Buttons): 40x40 Squircles centered at X=38px */
    .app-sidebar.is-collapsed .sidebar-item {
        width: 2.5rem !important;      /* 40px */
        height: 2.5rem !important;     /* 40px */
        min-width: 2.5rem !important;
        min-height: 2.5rem !important;
        max-width: 2.5rem !important;
        max-height: 2.5rem !important;
        padding: 0 !important;
        margin-left: auto !important;
        margin-right: auto !important;
        margin-top: 3px !important;
        margin-bottom: 3px !important;
        border-radius: 10px !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 0 !important;
        box-sizing: border-box !important;
        cursor: pointer !important;
        position: relative !important;
    }
    .app-sidebar.is-collapsed .sidebar-item .sidebar-item-inner {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        width: 100% !important;
        height: 100% !important;
        min-width: 0 !important;
        gap: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Center child div if wrapped (e.g. WhatsApp icon) */
    .app-sidebar.is-collapsed .sidebar-item-inner > div {
        margin: 0 auto !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
    }

    /* Primary Icons: strictly 18px block centered on X=38px */
    .app-sidebar.is-collapsed .sidebar-item > svg:not(.lucide-chevron-down):not([data-lucide="chevron-down"]):not(.sidebar-chevron),
    .app-sidebar.is-collapsed .sidebar-item > i:not(.lucide-chevron-down):not([data-lucide="chevron-down"]):not(.sidebar-chevron),
    .app-sidebar.is-collapsed .sidebar-item-inner > svg,
    .app-sidebar.is-collapsed .sidebar-item-inner > i,
    .app-sidebar.is-collapsed .sidebar-item-inner > div > svg {
        margin: 0 auto !important;
        width: 1.125rem !important;     /* 18px */
        height: 1.125rem !important;    /* 18px */
        flex-shrink: 0 !important;
        display: block !important;
    }

    /* Strictly hide all dropdown chevrons in collapsed rail */
    .app-sidebar.is-collapsed [data-lucide="chevron-down"],
    .app-sidebar.is-collapsed .lucide-chevron-down,
    .app-sidebar.is-collapsed svg.lucide-chevron-down,
    .app-sidebar.is-collapsed i[data-lucide="chevron-down"],
    .app-sidebar.is-collapsed .sidebar-chevron {
        display: none !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        position: absolute !important;
        pointer-events: none !important;
        opacity: 0 !important;
    }

    /* Strictly hide all text labels and badges in collapsed items */
    .app-sidebar.is-collapsed .sidebar-item > span,
    .app-sidebar.is-collapsed .sidebar-item-inner > span,
    .app-sidebar.is-collapsed .sidebar-item > div:not(.sidebar-item-inner) {
        display: none !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }

    /* Hide section titles and inline submenus in collapsed mode */
    .app-sidebar.is-collapsed .sidebar-nav > div > div[x-show="!sidebarCollapsed"],
    .app-sidebar.is-collapsed .sidebar-nav div[x-show*="Open && !sidebarCollapsed"] {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }

    /* 6. Active Squircle State (macOS Sonoma Selection) */
    .app-sidebar.is-collapsed .sidebar-item[aria-current="page"] {
        border-radius: 10px !important;
        box-shadow: 0 1px 3px rgba(0, 122, 255, 0.35) !important;
    }

    /* 7. Separator Lines: Subtle Hairline centered at X=38px */
    .app-sidebar.is-collapsed .sidebar-separator {
        width: 1.75rem !important;     /* 28px */
        margin-left: auto !important;
        margin-right: auto !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
        height: 1px !important;
        display: block !important;
        border-top-width: 1px !important;
        border-color: rgba(60, 60, 67, 0.08) !important;
    }
    .dark .app-sidebar.is-collapsed .sidebar-separator {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    /* 8. Bottom Plan Card Area: Strictly Toggle Collapsed vs Expanded */
    .app-sidebar.is-collapsed .sidebar-bottom {
        padding: 0.625rem 0 !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        overflow: hidden !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .app-sidebar.is-collapsed .sidebar-plan-collapsed {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        width: 100% !important;
        margin: 0 auto !important;
    }
    .app-sidebar.is-collapsed .sidebar-plan-collapsed a {
        margin: 0 auto !important;
    }
    .app-sidebar.is-collapsed .sidebar-plan-expanded {
        display: none !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        position: absolute !important;
        pointer-events: none !important;
    }

    /* Expanded Sidebar State for Plan Card */
    .app-sidebar:not(.is-collapsed) .sidebar-plan-collapsed {
        display: none !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }
    .app-sidebar:not(.is-collapsed) .sidebar-plan-expanded {
        display: block !important;
        width: 100% !important;
    }
}
</style>

{{-- Sidebar Navigation (Apple HIG / macOS Sonoma Style) --}}
<aside
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full lg:translate-x-0': !sidebarOpen,
        'lg:w-[76px] is-collapsed': sidebarCollapsed,
        'lg:w-[272px]': !sidebarCollapsed
    }"
    class="app-sidebar fixed inset-y-0 left-0 z-50 w-[336px] h-screen max-h-screen glass-nav flex flex-col justify-between transition-all duration-300 ease-out max-lg:rounded-r-[20px] max-lg:shadow-[0_20px_50px_rgba(0,0,0,0.25)] select-none">

    {{-- 1. Brand Logo Header (macOS Toolbar Height h-14) --}}
    <div class="sidebar-header h-14 flex items-center justify-between px-4 border-b border-black/5 dark:border-white/10 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group overflow-hidden">
            <div id="tour-active-business"
                class="w-8 h-8 rounded-[9px] bg-[#007AFF] text-white flex items-center justify-center shadow-[0_1px_2px_rgba(0,122,255,0.3)] group-hover:scale-105 active:scale-95 transition-all shrink-0">
                <i data-lucide="boxes" class="w-4 h-4 text-white"></i>
            </div>
            <div x-show="!sidebarCollapsed" x-transition.opacity class="sidebar-brand-text overflow-hidden whitespace-nowrap">
                <div class="font-semibold text-[15px] text-black dark:text-white tracking-tight leading-none">Cooca UMKM
                </div>
                <div class="text-[10px] text-black/45 dark:text-white/45 font-medium tracking-wide mt-0.5">Business OS
                </div>
            </div>
        </a>
        {{-- Mobile Drawer Close Button --}}
        <button @click="sidebarOpen = false" type="button" aria-label="Tutup Menu"
            class="lg:hidden text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white p-1.5 rounded-[8px] hover:bg-black/[0.05] dark:hover:bg-white/[0.06] active:scale-95 transition-all cursor-pointer">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    {{-- 2. Active Tenant / Business Switcher Capsule --}}
    @if ($activeBiz)
        <div class="sidebar-tenant-wrapper px-3 py-2 border-b border-black/5 dark:border-white/10 shrink-0">
            <div class="relative group"
                :title="sidebarCollapsed ? '{{ $activeBiz->name }} (Klik untuk Ganti Bisnis)' : ''">
                <div
                    class="sidebar-tenant-card p-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between transition-all hover:border-[#007AFF]/30">
                    <div class="sidebar-tenant-inner flex items-center gap-2.5 overflow-hidden">
                        @if ($activeBiz->logo_url)
                            <div
                                class="sidebar-tenant-icon w-7 h-7 rounded-[7px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 flex items-center justify-center shrink-0 overflow-hidden p-0.5">
                                <img src="{{ $activeBiz->logo_url }}" alt="{{ $activeBiz->name }}"
                                    class="w-full h-full object-contain">
                            </div>
                        @else
                            <div
                                class="sidebar-tenant-icon w-7 h-7 rounded-[7px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                            </div>
                        @endif
                        <div class="overflow-hidden" x-show="!sidebarCollapsed" x-transition.opacity>
                            <div
                                class="text-[9px] text-black/40 dark:text-white/40 font-medium uppercase tracking-wider">
                                Bisnis Aktif</div>
                            <div class="text-[12px] font-semibold text-black dark:text-white truncate">
                                {{ $activeBiz->name }}</div>
                        </div>
                    </div>
                    <a href="{{ route('businesses.select') }}" id="tour-switch-business" title="Ganti Bisnis"
                        x-show="!sidebarCollapsed"
                        class="p-1 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] rounded-[6px] text-black/40 hover:text-[#007AFF] dark:text-white/40 dark:hover:text-[#007AFF] transition-all active:scale-95 shrink-0">
                        <i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <template x-if="sidebarCollapsed">
                    <a href="{{ route('businesses.select') }}" class="absolute inset-0 z-10"
                        title="Ganti Bisnis: {{ $activeBiz->name }}"></a>
                </template>
            </div>
        </div>
    @endif

    {{-- 3. Navigation Links (Clean macOS Sidebar Architecture - No Card Boxes) --}}
    <nav class="sidebar-nav flex-1 overflow-y-auto px-2.5 py-3 space-y-2.5 min-h-0 overscroll-contain text-xs"
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

        {{-- ======================================================== --}}
        {{-- SEKSI 1: RINGKASAN & INTI (CORE)                         --}}
        {{-- ======================================================== --}}
        <div class="space-y-0.5">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-semibold tracking-wide uppercase text-black/40 dark:text-white/40 select-none">
                Ringkasan &amp; Inti
            </div>

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" id="tour-nav-dashboard"
                {{ request()->routeIs('dashboard') ? 'aria-current="page"' : '' }}
                :title="sidebarCollapsed ? 'Dashboard' : ''"
                class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] {{ request()->routeIs('dashboard') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                <i data-lucide="layout-dashboard"
                    class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Dashboard</span>
            </a>

            {{-- AI Assistant --}}
            @if (\App\Support\Context::hasPermission('ai.access'))
                <a href="{{ route('pos.ai.index') }}" id="tour-nav-ai"
                    {{ request()->routeIs('pos.ai.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'AI Assistant' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] {{ request()->routeIs('pos.ai.*') ? 'bg-[#AF52DE] text-white shadow-[0_1px_2px_rgba(175,82,222,0.25)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="bot"
                        class="w-4 h-4 {{ request()->routeIs('pos.ai.*') ? 'text-white' : 'text-[#AF52DE] dark:text-[#BF5AF2]' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>AI Assistant</span>
                </a>
            @endif

            {{-- Komunitas Owner --}}
            @if (\App\Support\Context::isOwner())
                <a href="#" id="tour-nav-community"
                    @click.prevent="openComingSoon({ title: 'Komunitas Owner', icon: 'users', desc: 'Forum diskusi eksklusif khusus para owner UMKM Cooca — berbagi tips, trik, dan strategi bisnis bersama.', color: 'amber' })"
                    {{ request()->routeIs('community.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Komunitas Owner (Segera)' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] {{ request()->routeIs('community.*') ? 'bg-[#FF9500] text-white shadow-[0_1px_2px_rgba(255,149,0,0.25)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="users"
                        class="w-4 h-4 {{ request()->routeIs('community.*') ? 'text-white' : 'text-[#FF9500] dark:text-[#FF9F0A]' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Komunitas Owner</span>
                    <span x-show="!sidebarCollapsed"
                        class="ml-auto text-[9px] px-1.5 py-0.5 rounded-full bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] font-semibold shrink-0">Segera</span>
                </a>
            @endif
        </div>

        {{-- ======================================================== --}}
        {{-- SEKSI 2: OPERASIONAL BISNIS                              --}}
        {{-- ======================================================== --}}
        @if ($canAccessSales || $canAccessInventory || $canAccessPurchasing || $canAccessCosting)
            <div class="space-y-0.5 pt-1.5">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-semibold tracking-wide uppercase text-black/40 dark:text-white/40 select-none">
                    Operasional Bisnis
                </div>
                <div x-show="sidebarCollapsed" class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                {{-- 1. KASIR & PENJUALAN --}}
                @if ($canAccessSales)
                    <div class="relative group" x-data="{ flyoutOpen: false }"
                        @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                        <button type="button" id="tour-group-sales" data-tour-group="sales"
                            @click="salesOpen = !salesOpen" role="button" :aria-expanded="salesOpen ? 'true' : 'false'"
                            :title="sidebarCollapsed ? 'Kasir & Penjualan' : ''"
                            class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isSalesRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                                <i data-lucide="shopping-cart"
                                    class="w-4 h-4 {{ $isSalesRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Kasir &amp; Penjualan</span>
                            </div>
                            <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                                class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                                :class="salesOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                        </button>

                        <div x-show="salesOpen && !sidebarCollapsed"
                            x-transition:enter="transition-all ease-out duration-150"
                            class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">

                            {{-- Subgroup: Operasional Kasir & Resto POS --}}
                            @if (\App\Support\Context::hasPermission('pos.terminal') || \App\Support\Context::isOwner())
                                <div class="px-2 pt-1 pb-0.5 text-[9.5px] font-bold tracking-wider uppercase text-black/35 dark:text-white/35 select-none">
                                    Kasir &amp; Resto POS
                                </div>
                            @endif

                            @if (\App\Support\Context::hasPermission('pos.terminal'))
                                <a href="{{ route('pos.terminal') }}" id="tour-nav-pos-terminal"
                                    {{ request()->routeIs('pos.terminal') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.terminal') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="calculator"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('pos.terminal') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Terminal Kasir POS</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('pos.terminal') || \App\Support\Context::hasPermission('pos.reports') || \App\Support\Context::hasPermission('pos.orders'))
                                <a href="{{ route('pos.orders.index') }}" id="tour-nav-pos-orders"
                                    {{ request()->routeIs('pos.orders.*') || request()->routeIs('pos.shifts.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.orders.*') || request()->routeIs('pos.shifts.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="shopping-bag"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('pos.orders.*') || request()->routeIs('pos.shifts.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Riwayat Transaksi &amp; Shift</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('pos.terminal') || \App\Support\Context::isOwner())
                                <a href="{{ route('pos.kitchen.index') }}" id="tour-nav-pos-kitchen"
                                    {{ request()->routeIs('pos.kitchen.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.kitchen.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="chef-hat"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('pos.kitchen.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Kitchen Display (KDS)</span>
                                </a>

                                <a href="{{ route('pos.tables.index') }}" id="tour-nav-pos-tables"
                                    {{ request()->routeIs('pos.tables.index') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.tables.index') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="layout-grid"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('pos.tables.index') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Manajemen Meja &amp; QR</span>
                                </a>

                                <a href="{{ route('pos.tables.qr-cards') }}" id="tour-nav-pos-qr-cards" target="_blank"
                                    {{ request()->routeIs('pos.tables.qr-cards') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.tables.qr-cards') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="qr-code"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('pos.tables.qr-cards') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Cetak Kartu QR Meja</span>
                                </a>

                            @endif

                            {{-- Subgroup: Penjualan & Penagihan B2B --}}
                            @if (\App\Support\Context::hasPermission('sales.view') || \App\Support\Context::hasPermission('invoices.view') || \App\Support\Context::hasPermission('sales.returns'))
                                <div class="px-2 pt-2 pb-0.5 text-[9.5px] font-bold tracking-wider uppercase text-black/35 dark:text-white/35 select-none">
                                    Penjualan &amp; Penagihan
                                </div>
                            @endif

                            @if (\App\Support\Context::hasPermission('sales.view'))
                                <a href="{{ route('sales.orders.index') }}" id="tour-nav-sales-orders"
                                    {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="check-square"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Pesanan &amp; Penawaran</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('sales.view'))
                                <a href="{{ route('sales.quotations.index') }}" id="tour-nav-sales-quotations"
                                    {{ request()->routeIs('sales.quotations.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('sales.quotations.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="file-signature" class="w-3.5 h-3.5 {{ request()->routeIs('sales.quotations.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Penawaran</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('invoices.view'))
                                <a href="{{ route('invoices.index') }}" id="tour-nav-invoices"
                                    {{ request()->routeIs('invoices.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('invoices.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="receipt"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('invoices.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Faktur &amp; Piutang</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('sales.view') || \App\Support\Context::hasPermission('sales.returns'))
                                <a href="{{ route('sales.returns.index') }}" id="tour-nav-sales-returns"
                                    {{ request()->routeIs('sales.returns.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('sales.returns.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="undo-2"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('sales.returns.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Retur Penjualan</span>
                                </a>
                            @endif

                            {{-- Subgroup: Pelanggan & Loyalitas CRM --}}
                            @if (\App\Support\Context::hasPermission('customers.view') || \App\Support\Context::hasPermission('crm.view'))
                                <div class="px-2 pt-2 pb-0.5 text-[9.5px] font-bold tracking-wider uppercase text-black/35 dark:text-white/35 select-none">
                                    Pelanggan &amp; Loyalitas
                                </div>

                                @if (\App\Support\Context::hasPermission('customers.view'))
                                <a href="{{ route('customers.index') }}" id="tour-nav-customers"
                                    {{ request()->routeIs('customers.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('customers.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="users"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('customers.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Pelanggan</span>
                                </a>
                                @endif

                                @if (\App\Support\Context::hasPermission('crm.view'))
                                <a href="{{ route('crm.members.index') }}" id="tour-nav-crm-members"
                                    {{ request()->routeIs('crm.members.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('crm.members.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="award"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('crm.members.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">CRM &amp; Loyalitas</span>
                                </a>

                                <a href="{{ route('crm.vouchers.index') }}" id="tour-nav-crm-vouchers"
                                    {{ request()->routeIs('crm.vouchers.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('crm.vouchers.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="ticket"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('crm.vouchers.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Voucher Diskon Promosi</span>
                                </a>
                                @endif
                            @endif
                        </div>

                        {{-- Flyout Menu on Collapsed Hover --}}
                        <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                            class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                            style="display: none;">
                            <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                                Kasir &amp; Penjualan
                            </div>
                            @if (\App\Support\Context::hasPermission('pos.terminal'))
                                <a href="{{ route('pos.terminal') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="calculator" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Terminal Kasir POS</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('pos.terminal') || \App\Support\Context::hasPermission('pos.reports') || \App\Support\Context::hasPermission('pos.orders'))
                                <a href="{{ route('pos.orders.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Riwayat Shift &amp; Transaksi</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('pos.terminal') || \App\Support\Context::isOwner())
                                <a href="{{ route('pos.kitchen.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="chef-hat" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                    <span>Kitchen Display (KDS)</span>
                                </a>
                                <a href="{{ route('pos.tables.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="layout-grid" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Manajemen Meja &amp; QR</span>
                                </a>
                                <a href="{{ route('pos.tables.qr-cards') }}" target="_blank"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="qr-code" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>Cetak Kartu QR Meja</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('sales.view'))
                                <a href="{{ route('sales.orders.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="check-square" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Pesanan &amp; Penawaran</span>
                                </a>
                                <a href="{{ route('sales.quotations.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="file-signature" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Penawaran</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('invoices.view'))
                                <a href="{{ route('invoices.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Faktur &amp; Piutang</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('sales.view') || \App\Support\Context::hasPermission('sales.returns'))
                                <a href="{{ route('sales.returns.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="undo-2" class="w-3.5 h-3.5 text-[#FF3B30]"></i>
                                    <span>Retur Penjualan</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('customers.view'))
                                <a href="{{ route('customers.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="users" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Pelanggan</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('crm.view'))
                                <a href="{{ route('crm.members.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="award" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                    <span>CRM &amp; Loyalitas</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- 2. PRODUK & INVENTORI --}}
                @if ($canAccessInventory)
                    <div class="relative group" x-data="{ flyoutOpen: false }"
                        @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                        <button type="button" id="tour-group-inventory" data-tour-group="inventory"
                            @click="inventoryOpen = !inventoryOpen" role="button"
                            :aria-expanded="inventoryOpen ? 'true' : 'false'"
                            :title="sidebarCollapsed ? 'Produk & Inventori' : ''"
                            class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ $isInventoryRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                                <i data-lucide="package"
                                    class="w-4 h-4 {{ $isInventoryRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Produk &amp; Inventori</span>
                            </div>
                            <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                                class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                                :class="inventoryOpen ? 'rotate-180 text-[#007AFF]' : ''"></i>
                        </button>

                        <div x-show="inventoryOpen && !sidebarCollapsed"
                            x-transition:enter="transition-all ease-out duration-150"
                            class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">

                            {{-- Subgroup: Katalog & Resep --}}
                            @if (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('materials.view'))
                                <div class="px-2 pt-1 pb-0.5 text-[9.5px] font-bold tracking-wider uppercase text-black/35 dark:text-white/35 select-none">
                                    Katalog &amp; Bahan
                                </div>
                            @endif

                            @if (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('pos.modifiers'))
                                @if (\App\Support\Context::hasPermission('products.view'))
                                <a href="{{ route('products.index') }}" id="tour-nav-products"
                                    {{ request()->routeIs('products.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('products.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="package"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('products.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Katalog Produk &amp; Resep</span>
                                </a>
                                @endif

                                @if (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('pos.modifiers'))
                                <a href="{{ route('pos.modifiers.index') }}" id="tour-nav-product-modifiers"
                                    {{ request()->routeIs('pos.modifiers.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('pos.modifiers.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="sliders"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('pos.modifiers.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Modifier &amp; Varian Produk</span>
                                </a>
                                @endif
                            @endif

                            @if (\App\Support\Context::hasPermission('materials.view'))
                                <a href="{{ route('materials.index') }}" id="tour-nav-materials"
                                    {{ request()->routeIs('materials.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('materials.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="boxes"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('materials.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Bahan Baku &amp; Harga</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('materials.view') || \App\Support\Context::isOwner())
                                <a href="{{ route('import.index') }}" id="tour-nav-import"
                                    {{ request()->routeIs('import.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('import.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="file-spreadsheet"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('import.*') ? 'text-white' : 'text-[#34C759]' }} shrink-0"></i>
                                    <span class="truncate">Import Data Excel</span>
                                </a>
                            @endif

                            {{-- Subgroup: Fisik & Pergudangan --}}
                            @if (\App\Support\Context::hasPermission('inventory.view') || \App\Support\Context::hasPermission('inventory.manage'))
                                <div class="px-2 pt-2 pb-0.5 text-[9.5px] font-bold tracking-wider uppercase text-black/35 dark:text-white/35 select-none">
                                    Stok &amp; Pergudangan
                                </div>
                            @endif

                            @if (\App\Support\Context::hasPermission('inventory.view'))
                                <a href="{{ route('inventory.stocks') }}" id="tour-nav-inventory-stocks"
                                    {{ request()->routeIs('inventory.stocks') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('inventory.stocks') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="layers"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('inventory.stocks') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Stok Real-Time</span>
                                </a>

                                <a href="{{ route('inventory.movements') }}" id="tour-nav-inventory-movements"
                                    {{ request()->routeIs('inventory.movements') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('inventory.movements') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="arrow-left-right"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('inventory.movements') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Mutasi Stok (Kartu Stok)</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('pos.modifiers'))
                                <a href="{{ route('pos.modifiers.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="sliders" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                    <span>Modifier &amp; Varian</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('inventory.manage'))
                                <a href="{{ route('inventory.transfers.index') }}" id="tour-nav-inventory-transfers"
                                    {{ request()->routeIs('inventory.transfers.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('inventory.transfers.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="repeat"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('inventory.transfers.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Transfer Stok Gudang</span>
                                </a>

                                <a href="{{ route('inventory.opnames.index') }}" id="tour-nav-inventory-opnames"
                                    {{ request()->routeIs('inventory.opnames.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('inventory.opnames.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="clipboard-check"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('inventory.opnames.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Stock Opname Fisik</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('inventory.view') || \App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::hasPermission('warehouse.view') || \App\Support\Context::hasPermission('warehouse.manage'))
                                <a href="{{ route('warehouse.index') }}" id="tour-nav-warehouse"
                                    {{ request()->routeIs('warehouse.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('warehouse.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="warehouse"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('warehouse.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Gudang &amp; Lokasi</span>
                                </a>
                            @endif
                        </div>

                        {{-- Flyout on Collapsed Hover --}}
                        <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                            class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                            style="display: none;">
                            <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                                Produk &amp; Inventori
                            </div>
                            @if (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('pos.modifiers'))
                                @if (\App\Support\Context::hasPermission('products.view'))
                                <a href="{{ route('products.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="package" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Katalog Produk</span>
                                </a>
                                @endif
                                @if (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('pos.modifiers'))
                                <a href="{{ route('pos.modifiers.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="sliders" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                    <span>Modifier &amp; Varian</span>
                                </a>
                                @endif
                            @endif
                            @if (\App\Support\Context::hasPermission('materials.view'))
                                <a href="{{ route('materials.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="boxes" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                    <span>Bahan Baku &amp; Harga</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('materials.view') || \App\Support\Context::isOwner())
                                <a href="{{ route('import.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>Import Excel</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('inventory.view'))
                                <a href="{{ route('inventory.stocks') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="layers" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Stok Real-Time</span>
                                </a>
                                <a href="{{ route('inventory.movements') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="arrow-left-right" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                    <span>Mutasi Stok</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('inventory.manage'))
                                <a href="{{ route('inventory.transfers.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="repeat" class="w-3.5 h-3.5 text-[#FF2D55]"></i>
                                    <span>Transfer Stok</span>
                                </a>
                                <a href="{{ route('inventory.opnames.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>Stock Opname Fisik</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('inventory.view') || \App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::hasPermission('warehouse.view'))
                                <a href="{{ route('warehouse.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="warehouse" class="w-3.5 h-3.5 text-[#8E8E93]"></i>
                                    <span>Gudang &amp; Lokasi</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- 3. PEMBELIAN & VENDOR --}}
                @if ($canAccessPurchasing)
                    <div class="relative group" x-data="{ flyoutOpen: false }"
                        @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                        <button type="button" id="tour-group-purchasing" data-tour-group="purchasing"
                            @click="purchasingOpen = !purchasingOpen" role="button"
                            :aria-expanded="purchasingOpen ? 'true' : 'false'"
                            :title="sidebarCollapsed ? 'Pembelian & Vendor' : ''"
                            class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ $isPurchasingRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                                <i data-lucide="truck"
                                    class="w-4 h-4 {{ $isPurchasingRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Pembelian &amp; Vendor</span>
                            </div>
                            <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                                class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                                :class="purchasingOpen ? 'rotate-180 text-[#007AFF]' : ''"></i>
                        </button>

                        <div x-show="purchasingOpen && !sidebarCollapsed"
                            x-transition:enter="transition-all ease-out duration-150"
                            class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                            @if (\App\Support\Context::hasPermission('purchasing.view'))
                                <a href="{{ route('purchase-orders.index') }}" id="tour-nav-purchase-orders"
                                    {{ request()->routeIs('purchase-orders.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('purchase-orders.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="file-text"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('purchase-orders.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Purchase Order (PO)</span>
                                </a>

                                <a href="{{ route('purchasing.bills.index') }}" id="tour-nav-purchasing-bills"
                                    {{ request()->routeIs('purchasing.bills.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('purchasing.bills.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="receipt"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('purchasing.bills.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Tagihan &amp; Hutang Supplier</span>
                                </a>

                                <a href="{{ route('purchase.returns.index') }}" id="tour-nav-purchase-returns"
                                    {{ request()->routeIs('purchase.returns.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('purchase.returns.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="corner-up-left"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('purchase.returns.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Retur Pembelian</span>
                                </a>
                            @endif

                        </div>

                        {{-- Flyout on Collapsed Hover --}}
                        <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                            class="fixed left-[84px] -mt-8 w-56 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                            style="display: none;">
                            <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                                Pembelian &amp; Vendor
                            </div>
                            @if (\App\Support\Context::hasPermission('purchasing.view'))
                                <a href="{{ route('purchase-orders.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Purchase Order (PO)</span>
                                </a>
                                <a href="{{ route('purchasing.bills.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Tagihan Supplier</span>
                                </a>
                                <a href="{{ route('purchase.returns.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="corner-up-left" class="w-3.5 h-3.5 text-[#FF3B30]"></i>
                                    <span>Retur Pembelian</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- 4. HPP & PRODUKSI --}}
                @if ($canAccessCosting)
                    <div class="relative group" x-data="{ flyoutOpen: false }"
                        @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                        <button type="button" id="tour-group-costing" data-tour-group="costing"
                            @click="costingOpen = !costingOpen" role="button"
                            :aria-expanded="costingOpen ? 'true' : 'false'"
                            :title="sidebarCollapsed ? 'HPP & Produksi' : ''"
                            class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ $isCostingRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                                <i data-lucide="calculator"
                                    class="w-4 h-4 {{ $isCostingRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>HPP &amp; Produksi</span>
                            </div>
                            <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                                class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                                :class="costingOpen ? 'rotate-180 text-[#007AFF]' : ''"></i>
                        </button>

                        <div x-show="costingOpen && !sidebarCollapsed"
                            x-transition:enter="transition-all ease-out duration-150"
                            class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                            @if (\App\Support\Context::hasPermission('costing.view_margin'))
                                <a href="{{ route('calculator.index') }}" id="tour-nav-calculator"
                                    {{ request()->routeIs('calculator.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('calculator.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="sparkles"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('calculator.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Kalkulator HPP 3-Pilar</span>
                                </a>
                            @endif

                            @if (
                                \App\Support\Context::hasPermission('labor_machines.view') ||
                                    \App\Support\Context::hasPermission('costing.view_margin'))
                                <a href="{{ route('labor-machines.index') }}" id="tour-nav-labor-machines"
                                    {{ request()->routeIs('labor-machines.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('labor-machines.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="users-2"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('labor-machines.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Upah Kerja &amp; Mesin</span>
                                </a>
                            @endif

                            @if (
                                \App\Support\Context::hasPermission('costing.view_margin') ||
                                    \App\Support\Context::hasPermission('reports.costing'))
                                <a href="{{ route('profitability.index') }}" id="tour-nav-profitability"
                                    {{ request()->routeIs('profitability.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('profitability.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="target"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('profitability.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">BEP &amp; Profitabilitas</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('costing.view_margin'))
                                <a href="{{ route('simulator.index') }}" id="tour-nav-simulator"
                                    {{ request()->routeIs('simulator.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('simulator.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="sliders"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('simulator.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Simulasi What-If</span>
                                </a>
                            @endif
                        </div>

                        {{-- Flyout on Collapsed Hover --}}
                        <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                            class="fixed left-[84px] -mt-8 w-56 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                            style="display: none;">
                            <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                                HPP &amp; Produksi
                            </div>
                            @if (\App\Support\Context::hasPermission('costing.view_margin'))
                                <a href="{{ route('calculator.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Kalkulator HPP 3-Pilar</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('labor_machines.view') || \App\Support\Context::hasPermission('costing.view_margin'))
                                <a href="{{ route('labor-machines.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="users-2" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                    <span>Upah Kerja &amp; Mesin</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('costing.view_margin') || \App\Support\Context::hasPermission('reports.costing'))
                                <a href="{{ route('profitability.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="target" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>BEP &amp; Profitabilitas</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('costing.view_margin'))
                                <a href="{{ route('simulator.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="sliders" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                    <span>Simulasi What-If</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- SEKSI 3: KEUANGAN & ANALITIK                             --}}
        {{-- ======================================================== --}}
        @if ($canAccessFinance || $canAccessReports)
            <div class="space-y-0.5 pt-1.5">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1.5 pb-1 text-[10px] font-semibold tracking-wider uppercase text-black/40 dark:text-white/40 select-none">
                    Keuangan &amp; Analitik
                </div>
                <div x-show="sidebarCollapsed" class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/5">
                </div>

                {{-- 1. KEUANGAN & KAS --}}
                @if ($canAccessFinance)
                    <div class="relative group" x-data="{ flyoutOpen: false }"
                        @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                        <button type="button" id="tour-group-finance" data-tour-group="finance"
                            @click="financeOpen = !financeOpen" role="button"
                            :aria-expanded="financeOpen ? 'true' : 'false'"
                            :title="sidebarCollapsed ? 'Keuangan & Kas' : ''"
                            class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ $isFinanceRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                                <i data-lucide="landmark"
                                    class="w-4 h-4 {{ $isFinanceRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Keuangan &amp; Kas</span>
                            </div>
                            <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                                class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                                :class="financeOpen ? 'rotate-180 text-[#007AFF]' : ''"></i>
                        </button>

                        <div x-show="financeOpen && !sidebarCollapsed"
                            x-transition:enter="transition-all ease-out duration-150"
                            class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('finance.cash_bank') || \App\Support\Context::hasPermission('expenses.view'))
                                <a href="{{ route('finance.cash-bank.index') }}" id="tour-nav-cash-bank"
                                    {{ request()->routeIs('finance.cash-bank.index') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('finance.cash-bank.index') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="landmark"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('finance.cash-bank.index') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Kas &amp; Rekening Bank</span>
                                </a>

                                <a href="{{ route('finance.cash-bank.ledger') }}" id="tour-nav-cash-ledger"
                                    {{ request()->routeIs('finance.cash-bank.ledger') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('finance.cash-bank.ledger') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="book"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('finance.cash-bank.ledger') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Buku Kas &amp; Ledger</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('expenses.view'))
                                <a href="{{ route('finance.expenses.index') }}" id="tour-nav-expenses"
                                    {{ request()->routeIs('finance.expenses.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('finance.expenses.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="wallet"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('finance.expenses.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Beban Operasional</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('finance.receivables') || \App\Support\Context::hasPermission('invoices.view'))
                                <a href="{{ route('finance.receivables') }}" id="tour-nav-receivables"
                                    {{ request()->routeIs('finance.receivables') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('finance.receivables') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="arrow-down-left"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('finance.receivables') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Piutang Usaha (AR Aging)</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('finance.payables') || \App\Support\Context::hasPermission('purchasing.view'))
                                <a href="{{ route('finance.payables') }}" id="tour-nav-payables"
                                    {{ request()->routeIs('finance.payables') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('finance.payables') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="arrow-up-right"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('finance.payables') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Hutang Usaha (AP Aging)</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('accounting.view'))
                                <a href="{{ route('finance.journals.index') }}" id="tour-nav-journals"
                                    {{ request()->routeIs('finance.journals.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('finance.journals.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="book-open"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('finance.journals.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Jurnal Akuntansi Otomatis</span>
                                </a>
                            @endif
                        </div>

                        {{-- Flyout on Collapsed Hover --}}
                        <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                            class="fixed left-[84px] -mt-8 w-56 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                            style="display: none;">
                            <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                                Keuangan &amp; Kas
                            </div>
                            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('finance.cash_bank') || \App\Support\Context::hasPermission('expenses.view'))
                                <a href="{{ route('finance.cash-bank.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="landmark" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Kas &amp; Rekening Bank</span>
                                </a>
                                <a href="{{ route('finance.cash-bank.ledger') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="book" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                    <span>Buku Kas &amp; Ledger</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('expenses.view'))
                                <a href="{{ route('finance.expenses.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="wallet" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                    <span>Beban Operasional</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('finance.receivables') || \App\Support\Context::hasPermission('invoices.view'))
                                <a href="{{ route('finance.receivables') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="arrow-down-left" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>Piutang Usaha</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('finance.payables') || \App\Support\Context::hasPermission('purchasing.view'))
                                <a href="{{ route('finance.payables') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-[#FF3B30]"></i>
                                    <span>Hutang Usaha</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('accounting.view'))
                                <a href="{{ route('finance.journals.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="book-open" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                    <span>Jurnal Akuntansi</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- 2. LAPORAN & ANALITIK --}}
                @if ($canAccessReports)
                    <div class="relative group" x-data="{ flyoutOpen: false }"
                        @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                        <button type="button" id="tour-group-reports" data-tour-group="reports"
                            @click="reportsOpen = !reportsOpen" role="button"
                            :aria-expanded="reportsOpen ? 'true' : 'false'"
                            :title="sidebarCollapsed ? 'Laporan & Analitik' : ''"
                            class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ $isReportsRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                                <i data-lucide="bar-chart-3"
                                    class="w-4 h-4 {{ $isReportsRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Laporan &amp; Analitik</span>
                            </div>
                            <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                                class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                                :class="reportsOpen ? 'rotate-180 text-[#007AFF]' : ''"></i>
                        </button>

                        <div x-show="reportsOpen && !sidebarCollapsed"
                            x-transition:enter="transition-all ease-out duration-150"
                            class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                            @if (\App\Support\Context::hasPermission('reports.view'))
                                <a href="{{ route('reports.index') }}" id="tour-nav-reports"
                                    {{ request()->routeIs('reports.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('reports.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="bar-chart-3"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Laporan &amp; Analitik Bisnis</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('pos.reports'))
                                <a href="{{ route('pos.reports.index') }}" id="tour-nav-pos-reports"
                                    {{ request()->routeIs('pos.reports.*') ? 'aria-current="page"' : '' }}
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('pos.reports.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="file-pie-chart"
                                        class="w-3.5 h-3.5 {{ request()->routeIs('pos.reports.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                    <span class="truncate">Laporan Kasir POS</span>
                                </a>
                            @endif
                        </div>

                        {{-- Flyout on Collapsed Hover --}}
                        <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                            class="fixed left-[84px] -mt-8 w-56 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                            style="display: none;">
                            <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                                Laporan &amp; Analitik
                            </div>
                            @if (\App\Support\Context::hasPermission('reports.view'))
                                <a href="{{ route('reports.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="bar-chart-3" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Laporan &amp; Analitik Bisnis</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('pos.reports'))
                                <a href="{{ route('pos.reports.index') }}"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="file-pie-chart" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>Laporan Kasir POS</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- SEKSI 4: SALURAN & PROMOSI (CHANNELS)                    --}}
        {{-- ======================================================== --}}
        @if ($canAccessChannels || \App\Support\Context::hasPermission('cms.manage'))
        <div class="space-y-0.5 pt-1.5">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1.5 pb-1 text-[10px] font-semibold tracking-wider uppercase text-black/40 dark:text-white/40 select-none">
                Saluran &amp; CMS
            </div>
            <div x-show="sidebarCollapsed" class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/5"></div>

            {{-- WhatsApp Gateway --}}
            @if ($canAccessChannels)
            <div class="relative group" x-data="{ flyoutOpen: false }" @mouseenter="if(sidebarCollapsed) flyoutOpen = true"
                @mouseleave="flyoutOpen = false">
                <button type="button" @click="whatsappOpen = !whatsappOpen" role="button"
                    :aria-expanded="whatsappOpen ? 'true' : 'false'"
                    :title="sidebarCollapsed ? 'WhatsApp Gateway' : ''"
                    class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ $isWhatsAppRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                    <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                        <div class="w-4 h-4 rounded-[4px] flex items-center justify-center text-[#25D366] shrink-0">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0zm0 21.785a9.874 9.874 0 0 1-5.032-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.861 9.861 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884z" />
                            </svg>
                        </div>
                        <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>WhatsApp Gateway</span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0" x-show="!sidebarCollapsed">
                        <span
                            class="text-[9px] px-1.5 py-0.2 rounded-full bg-[#25D366]/15 text-[#1b8742] dark:text-[#25D366] font-semibold border border-[#25D366]/30">Scan
                            WA</span>
                        <i data-lucide="chevron-down"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="whatsappOpen ? 'rotate-180 text-[#25D366]' : ''"></i>
                    </div>
                </button>

                <div x-show="whatsappOpen && !sidebarCollapsed"
                    x-transition:enter="transition-all ease-out duration-150"
                    class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                    <a href="{{ route('whatsapp.index') }}"
                        {{ request()->routeIs('whatsapp.index') ? 'aria-current="page"' : '' }}
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('whatsapp.index') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <i data-lucide="qr-code"
                            class="w-3.5 h-3.5 {{ request()->routeIs('whatsapp.index') ? 'text-white' : 'text-[#25D366]' }} shrink-0"></i>
                        <span class="truncate">Scan QR / Status</span>
                    </a>
                    <a href="{{ route('whatsapp.broadcast.index') }}"
                        {{ request()->routeIs('whatsapp.broadcast.*') ? 'aria-current="page"' : '' }}
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('whatsapp.broadcast.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <i data-lucide="send"
                            class="w-3.5 h-3.5 {{ request()->routeIs('whatsapp.broadcast.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                        <span class="truncate">Blast Promosi</span>
                    </a>
                    <a href="{{ route('whatsapp.logs.index') }}"
                        {{ request()->routeIs('whatsapp.logs.*') ? 'aria-current="page"' : '' }}
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('whatsapp.logs.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <i data-lucide="list"
                            class="w-3.5 h-3.5 {{ request()->routeIs('whatsapp.logs.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                        <span class="truncate">Log Pesan Struk</span>
                    </a>
                </div>

                {{-- Flyout on Collapsed Hover --}}
                <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                    class="fixed left-[84px] -mt-8 w-56 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                    style="display: none;">
                    <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                        WhatsApp Gateway
                    </div>
                    <a href="{{ route('whatsapp.index') }}"
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                        <i data-lucide="qr-code" class="w-3.5 h-3.5 text-[#25D366]"></i>
                        <span>Scan QR / Status</span>
                    </a>
                    <a href="{{ route('whatsapp.broadcast.index') }}"
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                        <i data-lucide="send" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Blast Promosi</span>
                    </a>
                    <a href="{{ route('whatsapp.logs.index') }}"
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                        <i data-lucide="list" class="w-3.5 h-3.5 text-[#8E8E93]"></i>
                        <span>Log Pesan Struk</span>
                    </a>
                </div>
            </div>
            @endif

            {{-- Landing Page CMS --}}
            @if (\App\Support\Context::hasPermission('cms.manage'))
            <a href="{{ route('landing-page.edit') }}" id="tour-nav-landing-page"
                {{ request()->routeIs('landing-page.*') ? 'aria-current="page"' : '' }}
                :title="sidebarCollapsed ? 'Landing Page' : ''"
                class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('landing-page.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                <i data-lucide="globe"
                    class="w-4 h-4 {{ request()->routeIs('landing-page.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Landing Page</span>
                <span x-show="!sidebarCollapsed"
                    class="ml-auto text-[9px] px-1.5 py-0.2 rounded-full {{ request()->routeIs('landing-page.*') ? 'bg-white/20 text-white' : 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20' }} font-semibold shrink-0">Live
                    CMS</span>
            </a>
            @endif
        </div>
        @endif

        {{-- ======================================================== --}}
        {{-- SEKSI 5: SISTEM & PENGATURAN                             --}}
        {{-- ======================================================== --}}
        <div class="space-y-0.5 pt-1.5">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1.5 pb-1 text-[10px] font-semibold tracking-wider uppercase text-black/40 dark:text-white/40 select-none">
                Sistem &amp; Pengaturan
            </div>
            <div x-show="sidebarCollapsed" class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/5"></div>

            {{-- Master Data --}}
            @if ($canAccessMasterData)
                <div class="relative group" x-data="{ flyoutOpen: false }"
                    @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                    <button type="button" id="tour-group-master-data" data-tour-group="master-data"
                        @click="masterDataOpen = !masterDataOpen" role="button"
                        :aria-expanded="masterDataOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Master Data' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ $isMasterDataRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="database"
                                class="w-4 h-4 {{ $isMasterDataRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Master Data</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="masterDataOpen ? 'rotate-180 text-[#007AFF]' : ''"></i>
                    </button>

                    <div x-show="masterDataOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        @if (\App\Support\Context::hasPermission('master_data.product_categories.view'))
                            <a href="{{ route('product-categories.index') }}" id="tour-nav-product-categories"
                                {{ request()->routeIs('product-categories.*') ? 'aria-current="page"' : '' }}
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('product-categories.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="folder"
                                    class="w-3.5 h-3.5 {{ request()->routeIs('product-categories.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                <span class="truncate">Kategori Produk</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('master_data.material_categories.view'))
                            <a href="{{ route('material-categories.index') }}" id="tour-nav-material-categories"
                                {{ request()->routeIs('material-categories.*') ? 'aria-current="page"' : '' }}
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('material-categories.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="layers"
                                    class="w-3.5 h-3.5 {{ request()->routeIs('material-categories.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                <span class="truncate">Kategori Bahan</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('master_data.units.view'))
                            <a href="{{ route('units.index') }}" id="tour-nav-units"
                                {{ request()->routeIs('units.*') ? 'aria-current="page"' : '' }}
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('units.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="scale"
                                    class="w-3.5 h-3.5 {{ request()->routeIs('units.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                <span class="truncate">Satuan &amp; Konversi</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('master_data.suppliers.view'))
                            <a href="{{ route('suppliers.index') }}" id="tour-nav-suppliers"
                                {{ request()->routeIs('suppliers.*') ? 'aria-current="page"' : '' }}
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('suppliers.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="truck"
                                    class="w-3.5 h-3.5 {{ request()->routeIs('suppliers.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                                <span class="truncate">Supplier</span>
                            </a>
                        @endif
                    </div>

                    {{-- Flyout on Collapsed Hover --}}
                    <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-56 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Master Data
                        </div>
                        @if (\App\Support\Context::hasPermission('master_data.product_categories.view'))
                            <a href="{{ route('product-categories.index') }}"
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="folder" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Kategori Produk</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('master_data.material_categories.view'))
                            <a href="{{ route('material-categories.index') }}"
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="layers" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Kategori Bahan</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('master_data.units.view'))
                            <a href="{{ route('units.index') }}"
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="scale" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Satuan &amp; Konversi</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('master_data.suppliers.view'))
                            <a href="{{ route('suppliers.index') }}"
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="truck" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Supplier</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Pengaturan Usaha --}}
            <a href="{{ route('profile.edit') }}" id="tour-nav-profile"
                {{ request()->routeIs('profile.edit') ? 'aria-current="page"' : '' }}
                :title="sidebarCollapsed ? 'Profil Saya' : ''"
                class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] {{ request()->routeIs('profile.edit') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                <i data-lucide="user-round" class="w-4 h-4 {{ request()->routeIs('profile.edit') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Profil Saya</span>
            </a>

            @if ($canAccessSettings)
                <a href="{{ route('settings.index') }}" id="tour-nav-settings"
                    {{ request()->routeIs('settings.*') && !request()->routeIs('settings.roles.*') && !request()->routeIs('roles.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Pengaturan Usaha' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('settings.*') && !request()->routeIs('settings.roles.*') && !request()->routeIs('roles.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="settings"
                        class="w-4 h-4 {{ request()->routeIs('settings.*') && !request()->routeIs('settings.roles.*') && !request()->routeIs('roles.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Pengaturan Usaha</span>
                </a>
            @endif

            {{-- Kontrol Akses & Role --}}
            @if ($canAccessRoles)
                <a href="{{ route('roles.index') }}" id="tour-nav-roles"
                    {{ request()->routeIs('roles.*') || request()->routeIs('settings.roles.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Kontrol Akses & Role' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('roles.*') || request()->routeIs('settings.roles.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="shield-check"
                        class="w-4 h-4 {{ request()->routeIs('roles.*') || request()->routeIs('settings.roles.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Kontrol Akses &amp; Role</span>
                </a>
            @endif

            {{-- Paket & Kuota --}}
            @if ($canAccessBilling)
                <a href="{{ route('billing') }}" id="tour-nav-billing"
                    {{ request()->routeIs('billing*') || request()->is('billing*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Paket & Kuota' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('billing*') || request()->is('billing*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="sparkles"
                        class="w-4 h-4 {{ request()->routeIs('billing*') || request()->is('billing*') ? 'text-white' : 'text-[#AF52DE]' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Paket &amp; Kuota</span>
                    <span x-show="!sidebarCollapsed"
                        class="ml-auto text-[9px] px-1.5 py-0.2 rounded-full {{ request()->routeIs('billing*') || request()->is('billing*') ? 'bg-white/20 text-white' : ($isCorePlan ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]') }} font-semibold uppercase shrink-0">
                        {{ $isCorePlan ? 'Core' : 'Free' }}
                    </span>
                </a>
            @endif

            {{-- Dukungan Produk --}}
            @if (\App\Support\Context::isOwner())
                <a href="{{ route('feedback.bugs.index') }}" id="tour-nav-feedback"
                    {{ request()->routeIs('feedback.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Dukungan Produk' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] active:opacity-80 {{ request()->routeIs('feedback.*') ? 'bg-[#007AFF] text-white font-medium shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="life-buoy"
                        class="w-4 h-4 {{ request()->routeIs('feedback.*') ? 'text-white' : 'text-[#007AFF]' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Dukungan Produk</span>
                </a>
            @endif
        </div>
    </nav>

    {{-- 4. Subscription Plan Card (Apple HIG Special Design Card) --}}
    <div class="sidebar-bottom p-2.5 border-t border-black/5 dark:border-white/10 shrink-0 bg-white/75 dark:bg-[#1C1C1E]/75 backdrop-blur-md">
        <!-- Collapsed Sidebar: Compact Squircle Action -->
        <div x-show="sidebarCollapsed" class="sidebar-plan-collapsed flex justify-center py-1">
            @if ($isCorePlan)
                <a href="{{ route('billing.limits') }}"
                    title="Cooca UMKM (∞ Unlimited) - Kelola Kuota"
                    class="w-10 h-10 rounded-[10px] bg-[#34C759]/12 border border-[#34C759]/30 text-[#34C759] dark:text-[#30D158] flex items-center justify-center hover:bg-[#34C759]/20 active:scale-95 transition-all shadow-2xs">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </a>
            @else
                <a href="{{ route('billing.patungan') }}"
                    title="Paket Free (Solo) - Tingkatkan ke Cooca UMKM"
                    class="w-10 h-10 rounded-[10px] bg-gradient-to-br from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center hover:opacity-90 active:scale-95 transition-all shadow-[0_2px_8px_rgba(0,122,255,0.3)]">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </a>
            @endif
        </div>

        <!-- Expanded Sidebar: Rich Apple HIG Card -->
        <div x-show="!sidebarCollapsed" class="sidebar-plan-expanded" x-transition.opacity>
            @if ($isCorePlan)
                {{-- Active Core Plan Card --}}
                <div class="relative overflow-hidden rounded-[12px] p-3 border border-[#34C759]/25 bg-gradient-to-br from-[#34C759]/10 via-[#30D158]/5 to-transparent dark:from-[#30D158]/15 dark:via-[#30D158]/5 dark:to-transparent space-y-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                    <div class="flex items-center justify-between gap-1.5">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse shrink-0"></span>
                            <span class="text-[12px] font-semibold text-black dark:text-white truncate">Cooca UMKM</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] border border-[#34C759]/25 shrink-0">
                            Aktif
                        </span>
                    </div>

                    <div class="space-y-1 text-[11px] text-black/60 dark:text-white/60">
                        <div class="flex items-center justify-between">
                            <span>Akses Fitur:</span>
                            <span class="font-semibold text-[#34C759] dark:text-[#30D158] flex items-center gap-1">
                                <i data-lucide="infinity" class="w-3 h-3"></i>
                                <span>∞ Unlimited</span>
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[10.5px]">
                            <span>Token AI (Top-up):</span>
                            <span class="font-medium text-black/80 dark:text-white/80 tabular-nums">
                                {{ number_format($navUsage['ai_tokens']['remaining'] ?? 0) }}
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('billing.limits') }}"
                        class="w-full h-7 px-2.5 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/75 dark:text-white/75 text-[11px] font-medium flex items-center justify-center gap-1 transition-all active:scale-[0.97]">
                        <span>Kelola Kuota &amp; Detail</span>
                        <i data-lucide="chevron-right" class="w-3 h-3 text-black/40 dark:text-white/40"></i>
                    </a>
                </div>
            @else
                {{-- Free Plan Upgrade Card (macOS Sonoma Frosted Glass + iOS Gradient) --}}
                <div class="relative overflow-hidden rounded-[12px] p-3 border border-black/5 dark:border-white/10 bg-gradient-to-br from-[#007AFF]/10 via-[#5856D6]/5 to-transparent dark:from-[#007AFF]/15 dark:via-[#5856D6]/10 dark:to-transparent space-y-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.03)]">
                    {{-- Header Badge & Title --}}
                    <div class="flex items-center justify-between gap-1.5">
                        <span class="text-[12px] font-semibold text-black dark:text-white truncate">Paket Free (Solo)</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A] border border-[#FF9500]/25 shrink-0">
                            Free
                        </span>
                    </div>

                    {{-- Usage Meters --}}
                    <div class="space-y-1.5 text-[11px]">
                        {{-- Katalog Produk --}}
                        <div class="space-y-0.5">
                            <div class="flex items-center justify-between text-[10.5px] text-black/60 dark:text-white/60">
                                <span>Katalog Produk:</span>
                                <span class="font-medium text-black/80 dark:text-white/80 tabular-nums">
                                    {{ $navUsage['products']['used'] ?? 0 }} / 50
                                </span>
                            </div>
                            <div class="w-full h-1 bg-black/5 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-[#007AFF] rounded-full transition-all duration-300"
                                    style="width: {{ min(100, $navUsage['products']['percent'] ?? 0) }}%"></div>
                            </div>
                        </div>

                        {{-- Resep / BOM --}}
                        <div class="space-y-0.5">
                            <div class="flex items-center justify-between text-[10.5px] text-black/60 dark:text-white/60">
                                <span>Resep / BOM:</span>
                                <span class="font-medium text-black/80 dark:text-white/80 tabular-nums">
                                    {{ $navUsage['recipes']['used'] ?? 0 }} / 20
                                </span>
                            </div>
                            <div class="w-full h-1 bg-black/5 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-[#5856D6] rounded-full transition-all duration-300"
                                    style="width: {{ min(100, $navUsage['recipes']['percent'] ?? 0) }}%"></div>
                            </div>
                        </div>

                        {{-- Invoice Bulan Ini --}}
                        <div class="space-y-0.5">
                            <div class="flex items-center justify-between text-[10.5px] text-black/60 dark:text-white/60">
                                <span>Invoice Bulan Ini:</span>
                                <span class="font-medium text-black/80 dark:text-white/80 tabular-nums">
                                    {{ $navUsage['invoices_this_month']['used'] ?? 0 }} / 10
                                </span>
                            </div>
                            <div class="w-full h-1 bg-black/5 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-[#AF52DE] rounded-full transition-all duration-300"
                                    style="width: {{ min(100, $navUsage['invoices_this_month']['percent'] ?? 0) }}%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Action CTA Button --}}
                    <div class="space-y-1 pt-0.5">
                        <a href="{{ route('billing.patungan') }}"
                            class="w-full h-7.5 px-3 rounded-[8px] bg-gradient-to-r from-[#007AFF] to-[#5856D6] hover:opacity-95 text-white text-[11px] font-semibold flex items-center justify-center gap-1.5 shadow-[0_2px_6px_rgba(0,122,255,0.25)] transition-all active:scale-[0.97] cursor-pointer">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>Upgrade</span>
                        </a>
                        <a href="{{ route('billing.limits') }}"
                            class="block text-center text-[10px] text-black/50 dark:text-white/50 hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition">
                            Tingkatkan ke Cooca UMKM ›
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</aside>
