@php
    $activeBiz = $activeBiz ?? \App\Support\Context::business();
    $navEntitlement = $navEntitlement ?? app(\App\Domain\Billing\EntitlementService::class);
    $navUsage = $navUsage ?? ($activeBiz ? $navEntitlement->getUsageSummary($activeBiz) : null);
    $isCorePlan = $navUsage['is_core'] ?? false;

    $activeModuleKey = \App\Support\Navigation\NavigationRegistry::getActiveModuleKey();

    $isPosRoute = (request()->routeIs('pos.*') && !request()->routeIs('pos.modifiers.*')) || $activeModuleKey === 'pos';
    $isB2bSalesRoute = request()->routeIs('sales.*') || request()->routeIs('invoices.*') || in_array($activeModuleKey, ['sales'], true);
    $isSalesRoute = $isPosRoute || $isB2bSalesRoute || request()->routeIs('customers.*') || request()->routeIs('crm.*') || in_array($activeModuleKey, ['sales', 'crm'], true);
    $isPurchasingRoute =
        request()->routeIs('purchasing.*') ||
        request()->routeIs('purchase-orders.*') ||
        request()->routeIs('purchase.returns.*') ||
        request()->routeIs('suppliers.*') ||
        $activeModuleKey === 'purchasing';
    $isInventoryRoute =
        request()->routeIs('products.*') ||
        request()->routeIs('services.*') ||
        request()->routeIs('materials.*') ||
        request()->routeIs('inventory.*') ||
        request()->routeIs('warehouse.*') ||
        request()->routeIs('import.*') ||
        request()->routeIs('product-categories.*') ||
        request()->routeIs('material-categories.*') ||
        request()->routeIs('units.*') ||
        request()->routeIs('marketplace-hub.*') ||
        request()->routeIs('pos.modifiers.*') ||
        in_array($activeModuleKey, ['products', 'materials', 'inventory'], true);
    $isFinanceRoute =
        request()->routeIs('finance.*') ||
        request()->routeIs('calculator.*') ||
        request()->routeIs('labor-machines.*') ||
        request()->routeIs('profitability.*') ||
        request()->routeIs('simulator.*') ||
        request()->routeIs('reports.*') ||
        request()->routeIs('pos.reports.*') ||
        request()->routeIs('hrm.*') ||
        request()->routeIs('tax.*') ||
        in_array($activeModuleKey, ['finance', 'accounting'], true);
    $isMarketingRoute =
        request()->routeIs('storefront.*') ||
        request()->routeIs('landing-page.*') ||
        request()->routeIs('whatsapp.*') ||
        request()->routeIs('social-media.*') ||
        in_array($activeModuleKey, ['communication', 'storefront'], true);
    $isChannelsRoute =
        request()->routeIs('storefront.*') ||
        request()->routeIs('landing-page.*') ||
        request()->routeIs('whatsapp.*') ||
        request()->routeIs('social-media.*') ||
        in_array($activeModuleKey, ['communication', 'storefront'], true);
    $isApprovalsRoute = request()->routeIs('approvals.*') || $activeModuleKey === 'approvals';
    $isSettingsRoute =
        request()->routeIs('settings.*') ||
        request()->routeIs('approval-rules.*') ||
        request()->routeIs('audit-logs.*') ||
        request()->routeIs('roles.*') ||
        request()->routeIs('billing.*') ||
        request()->routeIs('community.*') ||
        request()->routeIs('feedback.*') ||
        request()->routeIs('profile.*') ||
        $isApprovalsRoute;

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
        \App\Support\Context::hasPermission('reports.view') || \App\Support\Context::hasPermission('pos.reports');

    $canAccessHrm = \App\Support\Context::hasPermission('users.manage') || \App\Support\Context::isOwner();

    $canAccessChannels =
        \App\Support\Context::hasPermission('whatsapp.view') ||
        \App\Support\Context::hasPermission('whatsapp.manage');

    $canAccessStorefront =
        \App\Support\Context::hasPermission('storefront.orders.view') ||
        \App\Support\Context::hasPermission('storefront.manage') ||
        \App\Support\Context::hasPermission('storefront.shipping.manage') ||
        \App\Support\Context::hasPermission('storefront.reservations.manage') ||
        \App\Support\Context::hasPermission('storefront.po.manage') ||
        \App\Support\Context::hasPermission('cms.manage');

    $canAccessApprovals =
        \App\Support\Context::hasPermission('approvals.view') ||
        \App\Support\Context::hasPermission('approvals.manage') ||
        \App\Support\Context::isOwner();
    $canAccessSettings = \App\Support\Context::hasPermission('settings.view') || \App\Support\Context::isOwner();
    $canAccessAuditLogs = \App\Support\Context::hasPermission('audit_logs.view') || \App\Support\Context::isOwner() || $canAccessSettings;
    $canAccessRoles = \App\Support\Context::hasPermission('roles.view') || \App\Support\Context::isOwner();
    $canAccessBilling = \App\Support\Context::hasPermission('billing.view') || \App\Support\Context::isOwner();
    $canAccessMasterData =
        \App\Support\Context::hasPermission('master_data.material_categories.view') ||
        \App\Support\Context::hasPermission('master_data.product_categories.view') ||
        \App\Support\Context::hasPermission('master_data.units.view') ||
        \App\Support\Context::hasPermission('master_data.suppliers.view');

    $canAccessSystem =
        $canAccessMasterData ||
        $canAccessSettings ||
        $canAccessAuditLogs ||
        $canAccessRoles ||
        $canAccessBilling ||
        \App\Support\Context::isOwner();

    $sidebarBiz = \App\Support\Context::business();
    $sidebarStoreSetting = $sidebarBiz?->storeSetting;
    $sidebarShowReservation = request()->routeIs('storefront.reservations.*')
        || (
            $sidebarBiz
            && $sidebarBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RESERVATION)
            && ($sidebarStoreSetting->allow_reservation ?? true)
        );
    $sidebarShowShipping = request()->routeIs('storefront.shipping.*')
        || (
            $sidebarBiz
            && $sidebarBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_MERCHANT_SHIPPING)
            && ($sidebarStoreSetting->allow_delivery ?? true)
        );
@endphp

{{-- Apple HIG (macOS Sonoma & iOS 18) Sidebar Rail Styles --}}
<style>
    /* Calm Apple-style rhythm for the expanded navigation rail. */
    :root {
        --sidebar-row: 2.75rem;
        --sidebar-subrow: 2.5rem;
        --sidebar-icon: 1rem;
        --sidebar-radius: 0.625rem;
        --sidebar-group-gap: 0.75rem;
    }

    .sidebar-nav {
        padding: 1rem 0.75rem !important;
        line-height: 1.25rem;
    }

    .sidebar-nav .sidebar-item {
        min-height: var(--sidebar-row) !important;
        height: var(--sidebar-row);
        padding: 0.5rem 0.75rem !important;
        border-radius: var(--sidebar-radius) !important;
        line-height: 1.25rem;
        box-sizing: border-box;
    }

    .sidebar-nav>div {
        margin-bottom: var(--sidebar-group-gap);
    }

    .sidebar-nav .sidebar-item>i,
    .sidebar-nav .sidebar-item-inner>i,
    .sidebar-nav .sidebar-item-inner>div>i,
    .sidebar-nav .sidebar-item>svg,
    .sidebar-nav .sidebar-item-inner>svg {
        width: var(--sidebar-icon) !important;
        height: var(--sidebar-icon) !important;
        flex: 0 0 var(--sidebar-icon);
    }

    .sidebar-nav>div>div[x-show="!sidebarCollapsed"] {
        min-height: 1.25rem;
        display: flex;
        align-items: center;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
        letter-spacing: 0.06em;
    }

    /* Nested source-list groups: consistent inset, row height, and breathing room. */
    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"] {
        margin-left: 1rem !important;
        padding: 0.5rem 0.25rem 0.5rem 0.875rem !important;
        border-left-color: var(--border) !important;
    }

    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>a {
        min-height: var(--sidebar-subrow) !important;
        height: var(--sidebar-subrow);
        margin: 0.125rem 0;
        padding: 0.5rem 0.75rem !important;
        border-radius: var(--sidebar-radius) !important;
        gap: 0.625rem !important;
        line-height: 1.25rem;
        box-sizing: border-box;
    }

    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>a>i,
    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>a>svg {
        width: 0.875rem !important;
        height: 0.875rem !important;
        flex: 0 0 0.875rem;
    }

    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>div:not([x-show]) {
        margin-top: 0.625rem;
        margin-bottom: 0.25rem;
        padding: 0.25rem 0.75rem !important;
        min-height: 1.25rem;
        line-height: 1rem;
    }

    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>div:not([x-show])+a {
        margin-top: 0.125rem;
    }

    /* Every actionable navigation row shares one touch target and type scale. */
    .sidebar-nav a:not(.sidebar-item),
    .sidebar-nav button:not(.sidebar-item) {
        min-height: var(--sidebar-row) !important;
        height: var(--sidebar-row);
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        border-radius: var(--sidebar-radius) !important;
        font-size: 0.8125rem !important;
        line-height: 1.25rem !important;
        box-sizing: border-box;
    }

    .sidebar-nav a:not(.sidebar-item)>i,
    .sidebar-nav a:not(.sidebar-item)>svg,
    .sidebar-nav button:not(.sidebar-item)>i,
    .sidebar-nav button:not(.sidebar-item)>svg {
        width: var(--sidebar-icon) !important;
        height: var(--sidebar-icon) !important;
        flex: 0 0 var(--sidebar-icon);
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
            min-height: 2.75rem !important;
            padding-top: 0.625rem !important;
            padding-bottom: 0.625rem !important;
        }

        .app-sidebar:not(.is-collapsed) .sidebar-nav>div>div[x-show="!sidebarCollapsed"] {
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
            width: 2.5rem !important;
            /* 40px */
            height: 2.5rem !important;
            /* 40px */
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

        .app-sidebar.is-collapsed .sidebar-tenant-inner>div:not(.sidebar-tenant-icon),
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

        .app-sidebar.is-collapsed .sidebar-nav>div {
            margin-bottom: 0 !important;
        }

        .app-sidebar.is-collapsed .sidebar-nav div[x-show*="Open && !sidebarCollapsed"] {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        /* 5. Navigation Items (Links & Buttons): 40x40 Squircles centered at X=38px */
        .app-sidebar.is-collapsed .sidebar-item {
            width: 2.5rem !important;
            /* 40px */
            height: 2.5rem !important;
            /* 40px */
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
        .app-sidebar.is-collapsed .sidebar-item-inner>div {
            margin: 0 auto !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }

        /* Primary Icons: strictly 18px block centered on X=38px */
        .app-sidebar.is-collapsed .sidebar-item>svg:not(.lucide-chevron-down):not([data-lucide="chevron-down"]):not(.sidebar-chevron),
        .app-sidebar.is-collapsed .sidebar-item>i:not(.lucide-chevron-down):not([data-lucide="chevron-down"]):not(.sidebar-chevron),
        .app-sidebar.is-collapsed .sidebar-item-inner>svg,
        .app-sidebar.is-collapsed .sidebar-item-inner>i,
        .app-sidebar.is-collapsed .sidebar-item-inner>div>svg {
            margin: 0 auto !important;
            width: 1.125rem !important;
            /* 18px */
            height: 1.125rem !important;
            /* 18px */
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
        .app-sidebar.is-collapsed .sidebar-item>span,
        .app-sidebar.is-collapsed .sidebar-item-inner>span,
        .app-sidebar.is-collapsed .sidebar-item>div:not(.sidebar-item-inner) {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        /* Hide section titles and inline submenus in collapsed mode */
        .app-sidebar.is-collapsed .sidebar-nav>div>div[x-show="!sidebarCollapsed"],
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
            width: 1.75rem !important;
            /* 28px */
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

    /* Final source-list normalization: every expanded navigation row uses one geometry. */
    .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item,
    .app-sidebar:not(.is-collapsed) .sidebar-nav a:not(.sidebar-item),
    .app-sidebar:not(.is-collapsed) .sidebar-nav button:not(.sidebar-item) {
        display: flex !important;
        align-items: center !important;
        min-height: 2.75rem !important;
        height: 2.75rem !important;
        padding: 0.5rem 0.75rem !important;
        border-radius: 0.625rem !important;
        font-size: 0.8125rem !important;
        line-height: 1.25rem !important;
        box-sizing: border-box !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item>i,
    .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item>svg,
    .app-sidebar:not(.is-collapsed) .sidebar-nav a:not(.sidebar-item)>i,
    .app-sidebar:not(.is-collapsed) .sidebar-nav a:not(.sidebar-item)>svg,
    .app-sidebar:not(.is-collapsed) .sidebar-nav button:not(.sidebar-item)>i,
    .app-sidebar:not(.is-collapsed) .sidebar-nav button:not(.sidebar-item)>svg {
        width: 1rem !important;
        height: 1rem !important;
        flex: 0 0 1rem !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-nav div[x-show*="Open && !sidebarCollapsed"] {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>div:not([x-show]) {
        height: 1.5rem !important;
        min-height: 1.5rem !important;
        padding-top: 0.25rem !important;
        padding-bottom: 0.25rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.25rem !important;
        font-size: 0.6875rem !important;
        line-height: 1rem !important;
    }

    /* ====== Apple HIG polish & responsive hardening (added) ====== */
    /* 1. Seleksi sidebar ala Apple: tinted translusen, bukan pill solid + shadow */
    .sidebar-nav a[aria-current="page"],
    .sidebar-nav button[aria-current="page"] {
        background-color: rgba(0, 122, 255, 0.12) !important;
        color: #007AFF !important;
        box-shadow: none !important;
    }

    .dark .sidebar-nav a[aria-current="page"],
    .dark .sidebar-nav button[aria-current="page"] {
        background-color: rgba(10, 132, 255, 0.18) !important;
        color: #0A84FF !important;
    }

    .sidebar-nav a[aria-current="page"]>i,
    .sidebar-nav a[aria-current="page"]>svg {
        color: #007AFF !important;
    }

    .dark .sidebar-nav a[aria-current="page"]>i,
    .dark .sidebar-nav a[aria-current="page"]>svg {
        color: #0A84FF !important;
    }

    .sidebar-nav a[aria-current="page"]>span[class*="ml-auto"] {
        background-color: rgba(0, 122, 255, 0.14) !important;
        color: #007AFF !important;
    }

    .dark .sidebar-nav a[aria-current="page"]>span[class*="ml-auto"] {
        background-color: rgba(10, 132, 255, 0.2) !important;
        color: #0A84FF !important;
    }

    /* 2. Focus ring ala Apple untuk navigasi keyboard */
    .app-sidebar a:focus-visible,
    .app-sidebar button:focus-visible {
        outline: 2px solid rgba(0, 122, 255, 0.65) !important;
        outline-offset: 2px !important;
        border-radius: 0.5rem !important;
    }

    .dark .app-sidebar a:focus-visible,
    .dark .app-sidebar button:focus-visible {
        outline-color: rgba(10, 132, 255, 0.85) !important;
    }

    /* 3. Samsung-galaxy & lanskap pendek: sembunyikan kartu paket agar nav tetap terlihat */
    @media (max-height: 640px) {
        .sidebar-bottom {
            display: none !important;
        }
    }

    /* 4. Pastikan konten tenant wrapper tidak meluber di drawer sempit */
    .sidebar-tenant-wrapper {
        min-width: 0 !important;
    }

    .sidebar-tenant-inner {
        min-width: 0 !important;
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
    @keydown.esc.window="sidebarOpen = false"
    class="app-sidebar fixed inset-y-0 left-0 z-50 w-[min(336px,92vw)] h-screen max-h-screen glass-nav flex flex-col justify-between transition-all duration-300 ease-out max-lg:rounded-r-[20px] max-lg:shadow-[0_20px_50px_rgba(0,0,0,0.25)]">

    {{-- 1. Brand Logo Header (macOS Toolbar Height h-14) --}}
    <div
        class="sidebar-header h-14 flex items-center justify-between px-4 border-b border-black/5 dark:border-white/10 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group overflow-hidden">
            <div id="tour-active-business"
                class="w-8 h-8 rounded-[9px] bg-[#007AFF] text-white flex items-center justify-center shadow-[0_1px_2px_rgba(0,122,255,0.3)] group-hover:scale-105 active:scale-95 transition-all shrink-0">
                <i data-lucide="boxes" class="w-4 h-4 text-white"></i>
            </div>
            <div x-show="!sidebarCollapsed" x-transition.opacity
                class="sidebar-brand-text overflow-hidden whitespace-nowrap">
                <div class="font-semibold text-[15px] text-black dark:text-white tracking-tight leading-none">Cooca
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
                <a href="{{ route('businesses.select') }}" id="tour-switch-business"
                    aria-label="Ganti Bisnis: {{ $activeBiz->name }}"
                    class="sidebar-tenant-card group/card p-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between transition-all hover:border-[#007AFF]/40 hover:bg-[#007AFF]/[0.04] dark:hover:bg-white/[0.06] cursor-pointer">
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
                                class="text-[10px] text-black/40 dark:text-white/40 font-medium uppercase tracking-wider">
                                Bisnis Aktif</div>
                            <div class="text-[12px] font-semibold text-black dark:text-white truncate">
                                {{ $activeBiz->name }}</div>
                        </div>
                    </div>
                    <span aria-hidden="true" x-show="!sidebarCollapsed"
                        class="p-1.5 rounded-[7px] text-black/40 dark:text-white/40 group-hover/card:text-[#007AFF] dark:group-hover/card:text-[#0A84FF] transition-colors shrink-0">
                        <i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>
                    </span>
                </a>
                <template x-if="sidebarCollapsed">
                    <a href="{{ route('businesses.select') }}" class="absolute inset-0 z-10"
                        title="Ganti Bisnis: {{ $activeBiz->name }}"></a>
                </template>
            </div>
        </div>
    @endif

    {{-- 3. Navigation Links (Clean macOS Sidebar Architecture - No Card Boxes) --}}
        <nav aria-label="Navigasi utama"
        class="sidebar-nav flex-1 overflow-y-auto px-2.5 py-3 space-y-3 min-h-0 overscroll-contain text-xs"
        x-data="{
            posOpen: {{ $isPosRoute ? 'true' : 'false' }},
            b2bSalesOpen: {{ $isB2bSalesRoute ? 'true' : 'false' }},
            salesOpen: {{ $isSalesRoute ? 'true' : 'false' }},
            inventoryOpen: {{ $isInventoryRoute ? 'true' : 'false' }},
            purchasingOpen: {{ $isPurchasingRoute ? 'true' : 'false' }},
            marketingOpen: {{ ($isChannelsRoute || (isset($isMarketingRoute) && $isMarketingRoute)) ? 'true' : 'false' }},
            financeOpen: {{ $isFinanceRoute ? 'true' : 'false' }},
            reportsOpen: {{ (request()->routeIs('reports.*') || request()->routeIs('analytics.*') || request()->routeIs('pos.reports.*')) ? 'true' : 'false' }},
            settingsOpen: {{ $isSettingsRoute ? 'true' : 'false' }},
            activeFlyout: null
        }">

        {{-- ======================================================== --}}
        {{-- GRUP 1: OVERVIEW (RINGKASAN & DASHBOARD)                 --}}
        {{-- ======================================================== --}}
        <div class="space-y-1">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                Ringkasan &amp; Dashboard
            </div>
            <div x-show="sidebarCollapsed"
                class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
            </div>

            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'overview'"
                @mouseleave="activeFlyout = null">

                {{-- Primary Main Button: Dashboard (Visual Apple HIG Active Pill / Prominent Hero) --}}
                @if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('dashboard.view'))
                <a href="{{ route('dashboard') }}" id="tour-nav-dashboard"
                    {{ request()->routeIs('dashboard') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Dashboard Utama' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('dashboard') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>Dashboard Utama</span>
                </a>
                @endif

                {{-- Standalone Hub: Portal & Presensi (Accessible to all members) --}}
                <a href="{{ route('portal') }}" id="tour-nav-portal"
                    {{ request()->routeIs('portal') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Portal & Presensi' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 mt-1 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('portal') ? 'bg-[#34C759] text-white shadow-[0_2px_8px_rgba(52,199,89,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-[#34C759]/8 dark:hover:bg-[#34C759]/15 hover:text-[#34C759] dark:hover:text-[#30D158]' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('portal') ? 'bg-white/20 text-white' : 'bg-[#34C759]/12 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>Portal &amp; Presensi</span>
                </a>

                {{-- Standalone Top-Level: Asisten Cerdas AI --}}
                @if (\App\Support\Context::hasPermission('ai.access'))
                    <a href="{{ route('pos.ai.index') }}" id="tour-nav-ai"
                        {{ request()->routeIs('pos.ai.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? 'Asisten Cerdas AI' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 mt-1 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('pos.ai.*') ? 'bg-[#AF52DE] text-white shadow-[0_2px_8px_rgba(175,82,222,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-[#AF52DE]/8 dark:hover:bg-[#AF52DE]/15 hover:text-[#AF52DE] dark:hover:text-[#BF5AF2]' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('pos.ai.*') ? 'bg-white/20 text-white' : 'bg-[#AF52DE]/15 text-[#AF52DE] dark:bg-[#BF5AF2]/20 dark:text-[#BF5AF2]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        </span>
                        <div class="flex items-center justify-between flex-1 min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                            <span class="truncate tracking-tight">Asisten Cerdas AI</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-[#AF52DE]/15 text-[#AF52DE] dark:text-[#BF5AF2] shrink-0">AI</span>
                        </div>
                    </a>
                @endif

                {{-- Operational Notification Pills (Only when items exist) --}}
                @php
                    $pendingApprovalCount = $activeBiz && $canAccessApprovals ? \App\Models\ApprovalRequest::where('business_id', $activeBiz->id)->pending()->count() : 0;
                    $recentHighRiskCount = $activeBiz && $canAccessAuditLogs ? \App\Models\AuditLog::where('business_id', $activeBiz->id)->highRisk()->where('created_at', '>=', now()->subDays(7))->count() : 0;
                @endphp

                @if ($pendingApprovalCount > 0)
                    <div x-show="!sidebarCollapsed" class="ml-4 pl-3 py-1 space-y-0.5 border-l border-black/10 dark:border-white/10 mt-1">
                        <a href="{{ route('approvals.inbox') }}" id="tour-nav-approvals"
                            {{ request()->routeIs('approvals.*') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center justify-between px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-colors text-black/70 dark:text-white/70 hover:bg-black/[0.03] dark:hover:bg-white/[0.04]">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span>
                                <span class="truncate">Persetujuan Pending</span>
                            </div>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30 shrink-0">
                                {{ $pendingApprovalCount }}
                            </span>
                        </a>
                    </div>
                @endif

                {{-- Submenu Melayang (Collapsed Flyout) --}}
                <div x-show="sidebarCollapsed && activeFlyout === 'overview'" x-transition.opacity
                    class="fixed left-[84px] -mt-8 w-56 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50"
                    style="display: none;">
                    <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                        Ringkasan &amp; Dashboard
                    </div>
                    @if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('dashboard.view'))
                    <a href="{{ route('dashboard') }}"
                        class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span class="font-medium">Dashboard Utama</span>
                    </a>
                    @endif
                    <a href="{{ route('portal') }}"
                        class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-[#34C759]"></i>
                        <span class="font-medium">Portal &amp; Presensi</span>
                    </a>
                    @if (\App\Support\Context::hasPermission('ai.access'))
                        <a href="{{ route('pos.ai.index') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                            <span>Asisten Cerdas AI</span>
                        </a>
                    @endif
                    @if ($pendingApprovalCount > 0)
                        <a href="{{ route('approvals.inbox') }}"
                            class="sidebar-item flex items-center justify-between px-2.5 py-1.5 rounded-[7px] text-xs text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <div class="flex items-center gap-2 min-w-0">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Persetujuan Pending</span>
                            </div>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30 shrink-0">
                                {{ $pendingApprovalCount }}
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- GRUP 2A: KASIR & POS RESTORAN (POS OPERATIONS)           --}}
        {{-- ======================================================== --}}
        @if ($canAccessPos)
            <div class="space-y-1 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                    Kasir &amp; POS Resto
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'pos'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-group-pos" data-tour-group="pos"
                        @click="posOpen = !posOpen" role="button"
                        :aria-expanded="posOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Kasir & POS Resto' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isPosRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="calculator"
                                class="w-4 h-4 {{ $isPosRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Kasir &amp; POS Resto</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="posOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    {{-- Submenu Terbuka (Expanded) --}}
                    <div x-show="posOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        {{-- Buka Kasir POS --}}
                        @if (\App\Support\Context::hasPermission('pos.terminal'))
                            <a href="{{ route('pos.terminal') }}" id="tour-nav-pos-terminal"
                                {{ request()->routeIs('pos.terminal') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.terminal') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="calculator" class="w-3.5 h-3.5 {{ request()->routeIs('pos.terminal') ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }} shrink-0"></i>
                                <span class="truncate font-semibold">Buka Kasir POS</span>
                            </a>
                        @endif

                        {{-- Transaksi Kasir & Shift --}}
                        @if (\App\Support\Context::hasPermission('pos.orders') || \App\Support\Context::hasPermission('pos.terminal'))
                            <a href="{{ route('pos.orders.index') }}" id="tour-nav-pos-orders"
                                {{ request()->routeIs('pos.orders.*') && !request()->routeIs('pos.orders.kitchen') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.orders.*') && !request()->routeIs('pos.orders.kitchen') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 {{ request()->routeIs('pos.orders.*') && !request()->routeIs('pos.orders.kitchen') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Transaksi Kasir &amp; Shift</span>
                            </a>
                        @endif

                        {{-- Layar Dapur (KDS) --}}
                        @if (\App\Support\Context::hasPermission('pos.kitchen'))
                            <a href="{{ route('pos.kitchen.index') }}" id="tour-nav-pos-kitchen"
                                {{ request()->routeIs('pos.kitchen.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.kitchen.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="chef-hat" class="w-3.5 h-3.5 {{ request()->routeIs('pos.kitchen.*') ? 'text-white' : 'text-[#FF9500]' }} shrink-0"></i>
                                <span class="truncate">Layar Dapur (KDS)</span>
                            </a>
                        @endif

                        {{-- Meja & QR Resto --}}
                        @if (\App\Support\Context::hasPermission('pos.tables'))
                            <a href="{{ route('pos.tables.index') }}" id="tour-nav-pos-tables"
                                {{ request()->routeIs('pos.tables.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.tables.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="layout-grid" class="w-3.5 h-3.5 {{ request()->routeIs('pos.tables.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Meja &amp; QR Resto</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu Melayang (Collapsed Flyout) --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'pos'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Kasir &amp; POS Resto
                        </div>
                        @if (\App\Support\Context::hasPermission('pos.terminal'))
                            <a href="{{ route('pos.terminal') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="calculator" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span class="font-semibold text-[#007AFF]">Buka Kasir POS</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('pos.orders') || \App\Support\Context::hasPermission('pos.terminal'))
                            <a href="{{ route('pos.orders.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Transaksi Kasir &amp; Shift</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('pos.kitchen'))
                            <a href="{{ route('pos.kitchen.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="chef-hat" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Layar Dapur (KDS)</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('pos.tables'))
                            <a href="{{ route('pos.tables.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="layout-grid" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Meja &amp; QR Resto</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 2B: PENJUALAN B2B & FAKTUR (B2B SALES & INVOICES)   --}}
        {{-- ======================================================== --}}
        @if ($canAccessB2bSales)
            <div class="space-y-1 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                    Penjualan B2B &amp; Faktur
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'b2bSales'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-group-b2b" data-tour-group="b2b"
                        @click="b2bSalesOpen = !b2bSalesOpen" role="button"
                        :aria-expanded="b2bSalesOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Penjualan B2B & Faktur' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isB2bSalesRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="file-text"
                                class="w-4 h-4 {{ $isB2bSalesRoute ? 'text-[#5856D6]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Penjualan B2B &amp; Faktur</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="b2bSalesOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    {{-- Submenu Terbuka (Expanded) --}}
                    <div x-show="b2bSalesOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        @if (\App\Support\Context::hasPermission('sales.view'))
                            <a href="{{ route('sales.orders.index') }}" id="tour-nav-sales-orders"
                                {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') && !request()->routeIs('sales.quotations.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') && !request()->routeIs('sales.quotations.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="check-square" class="w-3.5 h-3.5 {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') && !request()->routeIs('sales.quotations.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Pesanan Penjualan (SO)</span>
                            </a>

                            <a href="{{ route('sales.quotations.index') }}" id="tour-nav-sales-quotations"
                                {{ request()->routeIs('sales.quotations.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('sales.quotations.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="file-signature" class="w-3.5 h-3.5 {{ request()->routeIs('sales.quotations.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Surat Penawaran</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.index') }}" id="tour-nav-invoices"
                                {{ request()->routeIs('invoices.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('invoices.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 {{ request()->routeIs('invoices.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Faktur Penjualan (Invoice)</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('sales.returns'))
                            <a href="{{ route('sales.returns.index') }}" id="tour-nav-sales-returns"
                                {{ request()->routeIs('sales.returns.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('sales.returns.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="undo-2" class="w-3.5 h-3.5 {{ request()->routeIs('sales.returns.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Retur Penjualan</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu Melayang (Collapsed Flyout) --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'b2bSales'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Penjualan B2B &amp; Faktur
                        </div>
                        @if (\App\Support\Context::hasPermission('sales.view'))
                            <a href="{{ route('sales.orders.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="check-square" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Pesanan Penjualan (SO)</span>
                            </a>
                            <a href="{{ route('sales.quotations.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="file-signature" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Surat Penawaran</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Faktur Penjualan</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('sales.returns'))
                            <a href="{{ route('sales.returns.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="undo-2" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Retur Penjualan</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 3: PRODUK & LOGISTIK (PRODUCTS, BOM & WAREHOUSE)    --}}
        {{-- ======================================================== --}}
        @if ($canAccessInventory)
            <div class="space-y-1 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                    Produk &amp; Logistik
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'inventory'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-group-inventory" data-tour-group="inventory"
                        @click="inventoryOpen = !inventoryOpen" role="button"
                        :aria-expanded="inventoryOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Produk & Logistik' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isInventoryRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="box"
                                class="w-4 h-4 {{ $isInventoryRoute ? 'text-[#FF9500]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Produk &amp; Logistik</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="inventoryOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    {{-- Submenu Terbuka (Expanded) --}}
                    <div x-show="inventoryOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        @if (\App\Support\Context::hasPermission('products.view'))
                            <a href="{{ route('products.index') }}" id="tour-nav-products"
                                {{ request()->routeIs('products.*') || request()->routeIs('services.*') || request()->routeIs('pos.modifiers.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('products.*') || request()->routeIs('services.*') || request()->routeIs('pos.modifiers.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="package" class="w-3.5 h-3.5 {{ request()->routeIs('products.*') || request()->routeIs('services.*') || request()->routeIs('pos.modifiers.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate font-semibold">Katalog Produk &amp; Menu</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('marketplace.view') || \App\Support\Context::isOwner())
                            <a href="{{ route('marketplace-hub.index') }}" id="tour-nav-marketplace-hub"
                                {{ request()->routeIs('marketplace-hub.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('marketplace-hub.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 {{ request()->routeIs('marketplace-hub.*') ? 'text-white' : 'text-[#FF2D55]' }} shrink-0"></i>
                                <span class="truncate font-medium">Integrasi Marketplace</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('materials.view'))
                            <a href="{{ route('materials.index') }}" id="tour-nav-materials"
                                {{ request()->routeIs('materials.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('materials.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="layers" class="w-3.5 h-3.5 {{ request()->routeIs('materials.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Bahan Baku &amp; Resep</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('inventory.view'))
                            <a href="{{ route('inventory.stocks') }}" id="tour-nav-inventory-stocks"
                                {{ request()->routeIs('inventory.stocks') || request()->routeIs('warehouse.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inventory.stocks') || request()->routeIs('warehouse.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="boxes" class="w-3.5 h-3.5 {{ request()->routeIs('inventory.stocks') || request()->routeIs('warehouse.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Stok &amp; Multi-Gudang</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('inventory.manage'))
                            <a href="{{ route('inventory.transfers.index') }}" id="tour-nav-inventory-transfers"
                                {{ request()->routeIs('inventory.transfers.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inventory.transfers.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="arrow-left-right" class="w-3.5 h-3.5 {{ request()->routeIs('inventory.transfers.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Transfer Stok Gudang</span>
                            </a>

                            <a href="{{ route('inventory.opnames.index') }}" id="tour-nav-inventory-opnames"
                                {{ request()->routeIs('inventory.opnames.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inventory.opnames.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="clipboard-check" class="w-3.5 h-3.5 {{ request()->routeIs('inventory.opnames.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Stock Opname Fisik</span>
                            </a>

                            <a href="{{ route('inventory.movements') }}" id="tour-nav-inventory-movements"
                                {{ request()->routeIs('inventory.movements') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inventory.movements') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="history" class="w-3.5 h-3.5 {{ request()->routeIs('inventory.movements') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Kartu Mutasi Stok</span>
                            </a>
                        @endif

                        @if ($canAccessMasterData)
                            <div class="pt-1.5 pb-0.5 px-2 text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                                Master Data
                            </div>

                            @if (\App\Support\Context::hasPermission('master_data.product_categories.view'))
                                <a href="{{ route('product-categories.index') }}"
                                    {{ request()->routeIs('product-categories.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('product-categories.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="tag" class="w-3.5 h-3.5 {{ request()->routeIs('product-categories.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Kategori Produk</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('master_data.material_categories.view'))
                                <a href="{{ route('material-categories.index') }}"
                                    {{ request()->routeIs('material-categories.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('material-categories.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="tags" class="w-3.5 h-3.5 {{ request()->routeIs('material-categories.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Kategori Bahan</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('master_data.units.view'))
                                <a href="{{ route('units.index') }}"
                                    {{ request()->routeIs('units.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('units.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="scale" class="w-3.5 h-3.5 {{ request()->routeIs('units.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Satuan Ukur</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('materials.view') || \App\Support\Context::hasPermission('products.view') || \App\Support\Context::isOwner())
                                <a href="{{ route('import.index') }}" id="tour-nav-import"
                                    {{ request()->routeIs('import.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('import.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 {{ request()->routeIs('import.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Impor / Ekspor Excel</span>
                                </a>
                            @endif
                        @endif
                    </div>

                    {{-- Submenu Melayang (Collapsed Flyout) --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'inventory'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Produk &amp; Logistik
                        </div>
                        @if (\App\Support\Context::hasPermission('products.view'))
                            <a href="{{ route('products.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="package" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Katalog Produk &amp; Menu</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('marketplace.view') || \App\Support\Context::isOwner())
                            <a href="{{ route('marketplace-hub.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-[#FF2D55]"></i>
                                <span>Integrasi Marketplace</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('materials.view'))
                            <a href="{{ route('materials.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="layers" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Bahan Baku &amp; Resep</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('inventory.view'))
                            <a href="{{ route('inventory.stocks') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="boxes" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Stok &amp; Multi-Gudang</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('inventory.manage'))
                            <a href="{{ route('inventory.transfers.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="arrow-left-right" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                <span>Transfer Stok Gudang</span>
                            </a>
                            <a href="{{ route('inventory.opnames.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Opname Stok Fisik</span>
                            </a>
                            <a href="{{ route('inventory.movements') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="history" class="w-3.5 h-3.5 text-[#30B0C7]"></i>
                                <span>Kartu Mutasi Stok</span>
                            </a>
                        @endif
                        @if ($canAccessMasterData)
                            <div class="px-2.5 pt-1.5 pb-0.5 text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 border-t border-black/5 dark:border-white/10 mt-1">
                                Master Data Logistik
                            </div>
                            @if (\App\Support\Context::hasPermission('master_data.product_categories.view'))
                                <a href="{{ route('product-categories.index') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="tag" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Kategori Produk</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('master_data.material_categories.view'))
                                <a href="{{ route('material-categories.index') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="tags" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>Kategori Bahan</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('master_data.units.view'))
                                <a href="{{ route('units.index') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="scale" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                    <span>Satuan Ukur</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('materials.view') || \App\Support\Context::hasPermission('products.view') || \App\Support\Context::isOwner())
                                <a href="{{ route('import.index') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-[#30B0C7]"></i>
                                    <span>Impor / Ekspor Excel</span>
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 4: PEMBELIAN & SUPPLIER                            --}}
        {{-- ======================================================== --}}
        @if ($canAccessPurchasing)
            <div class="space-y-1 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                    Pembelian &amp; Supplier
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'purchasing'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-group-purchasing" data-tour-group="purchasing"
                        @click="purchasingOpen = !purchasingOpen" role="button"
                        :aria-expanded="purchasingOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Pembelian & Supplier' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isPurchasingRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="truck"
                                class="w-4 h-4 {{ $isPurchasingRoute ? 'text-[#30B0C7]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Pembelian &amp; Supplier</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="purchasingOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    {{-- Submenu Terbuka (Expanded) --}}
                    <div x-show="purchasingOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        @if (\App\Support\Context::hasPermission('purchasing.view'))
                            <a href="{{ route('purchase-orders.index') }}" id="tour-nav-purchase-orders"
                                {{ request()->routeIs('purchase-orders.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('purchase-orders.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 {{ request()->routeIs('purchase-orders.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Pesanan Pembelian (PO)</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('purchasing.bills'))
                            <a href="{{ route('purchasing.bills.index') }}"
                                {{ request()->routeIs('purchasing.bills.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('purchasing.bills.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 {{ request()->routeIs('purchasing.bills.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Tagihan Supplier</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('master_data.suppliers.view'))
                            <a href="{{ route('suppliers.index') }}" id="tour-nav-suppliers"
                                {{ request()->routeIs('suppliers.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('suppliers.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 {{ request()->routeIs('suppliers.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Supplier &amp; Pemasok</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('purchase.returns'))
                            <a href="{{ route('purchase.returns.index') }}"
                                {{ request()->routeIs('purchase.returns.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('purchase.returns.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="undo" class="w-3.5 h-3.5 {{ request()->routeIs('purchase.returns.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Retur Pembelian</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu Melayang (Collapsed Flyout) --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'purchasing'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Pembelian &amp; Supplier
                        </div>
                        @if (\App\Support\Context::hasPermission('purchasing.view'))
                            <a href="{{ route('purchase-orders.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Pesanan Pembelian (PO)</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('purchasing.bills'))
                            <a href="{{ route('purchasing.bills.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Tagihan Supplier</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('master_data.suppliers.view'))
                            <a href="{{ route('suppliers.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Supplier &amp; Pemasok</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('purchase.returns'))
                            <a href="{{ route('purchase.returns.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="undo" class="w-3.5 h-3.5 text-[#FF3B30]"></i>
                                <span>Retur Pembelian</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 5: PELANGGAN & PEMASARAN                            --}}
        {{-- ======================================================== --}}
        @if ($canAccessCrm || $canAccessStorefront || $canAccessChannels)
            <div class="space-y-1 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                    Pelanggan &amp; Pemasaran
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'marketing'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-group-channels" data-tour-group="channels"
                        @click="marketingOpen = !marketingOpen" role="button"
                        :aria-expanded="marketingOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Pelanggan & Pemasaran' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isChannelsRoute || (isset($isMarketingRoute) && $isMarketingRoute) ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="megaphone"
                                class="w-4 h-4 {{ $isChannelsRoute || (isset($isMarketingRoute) && $isMarketingRoute) ? 'text-[#FF2D55]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Pelanggan &amp; Pemasaran</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="marketingOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    {{-- Submenu Terbuka (Expanded) --}}
                    <div x-show="marketingOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        @if (\App\Support\Context::hasPermission('customers.view'))
                            <a href="{{ route('customers.index') }}" id="tour-nav-customers"
                                {{ request()->routeIs('customers.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('customers.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="users" class="w-3.5 h-3.5 {{ request()->routeIs('customers.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Data Pelanggan</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('crm.view'))
                            <a href="{{ route('crm.members.index') }}" id="tour-nav-crm-members"
                                {{ request()->routeIs('crm.members.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('crm.members.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="award" class="w-3.5 h-3.5 {{ request()->routeIs('crm.members.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Member &amp; Loyalitas</span>
                            </a>

                            <a href="{{ route('crm.vouchers.index') }}" id="tour-nav-crm-vouchers"
                                {{ request()->routeIs('crm.vouchers.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('crm.vouchers.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="ticket" class="w-3.5 h-3.5 {{ request()->routeIs('crm.vouchers.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Voucher Diskon Promosi</span>
                            </a>
                        @endif

                        @if ($canAccessStorefront)
                            <div class="pt-1.5 pb-0.5 px-2 text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                                Toko Online
                            </div>

                            <a href="{{ route('storefront.orders.index') }}"
                                {{ request()->routeIs('storefront.orders.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('storefront.orders.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 {{ request()->routeIs('storefront.orders.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Pesanan Toko Online</span>
                            </a>

                            <a href="{{ route('landing-page.edit') }}"
                                {{ request()->routeIs('landing-page.edit') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('landing-page.edit') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="globe" class="w-3.5 h-3.5 {{ request()->routeIs('landing-page.edit') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Landing Page Usaha</span>
                            </a>

                            <a href="{{ route('landing-page.popup.edit') }}"
                                {{ request()->routeIs('landing-page.popup.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('landing-page.popup.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="megaphone" class="w-3.5 h-3.5 {{ request()->routeIs('landing-page.popup.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Pop Up Promo</span>
                            </a>

                            @if ($sidebarShowReservation)
                                <a href="{{ route('storefront.reservations.index') }}"
                                    {{ request()->routeIs('storefront.reservations.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('storefront.reservations.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 {{ request()->routeIs('storefront.reservations.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Reservasi Meja</span>
                                </a>
                            @endif

                            @if ($sidebarShowShipping)
                                <a href="{{ route('storefront.shipping.index') }}"
                                    {{ request()->routeIs('storefront.shipping.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('storefront.shipping.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="truck" class="w-3.5 h-3.5 {{ request()->routeIs('storefront.shipping.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Pengaturan Ongkir</span>
                                </a>
                            @endif

                            <a href="{{ route('storefront.settings.index') }}"
                                {{ request()->routeIs('storefront.settings.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('storefront.settings.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="store" class="w-3.5 h-3.5 {{ request()->routeIs('storefront.settings.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Pengaturan Toko Online</span>
                            </a>
                        @endif

                        @if ($canAccessChannels)
                            <div class="pt-1.5 pb-0.5 px-2 text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                                WhatsApp &amp; Medsos
                            </div>

                            <a href="{{ route('whatsapp.index') }}"
                                {{ request()->routeIs('whatsapp.index') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('whatsapp.index') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="message-square" class="w-3.5 h-3.5 {{ request()->routeIs('whatsapp.index') ? 'text-white' : 'text-[#34C759]' }} shrink-0"></i>
                                <span class="truncate">WhatsApp Bisnis</span>
                            </a>

                            <a href="{{ route('whatsapp.broadcast.index') }}"
                                {{ request()->routeIs('whatsapp.broadcast.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('whatsapp.broadcast.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="send" class="w-3.5 h-3.5 {{ request()->routeIs('whatsapp.broadcast.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Broadcast Pesan WA</span>
                            </a>

                            <a href="{{ route('whatsapp.logs.index') }}"
                                {{ request()->routeIs('whatsapp.logs.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('whatsapp.logs.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="list" class="w-3.5 h-3.5 {{ request()->routeIs('whatsapp.logs.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Riwayat Pesan WA</span>
                            </a>

                            <a href="{{ route('social-media.index') }}"
                                {{ request()->routeIs('social-media.index') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.index') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="share-2" class="w-3.5 h-3.5 {{ request()->routeIs('social-media.index') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Media Sosial Omnichannel</span>
                            </a>

                            <a href="{{ route('social-media.posts.index') }}"
                                {{ request()->routeIs('social-media.posts.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.posts.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5 {{ request()->routeIs('social-media.posts.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Konten &amp; Jadwal</span>
                            </a>

                            <a href="{{ route('social-media.calendar') }}"
                                {{ request()->routeIs('social-media.calendar') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.calendar') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 {{ request()->routeIs('social-media.calendar') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Kalender Konten</span>
                            </a>

                            <a href="{{ route('social-media.inbox.index') }}"
                                {{ request()->routeIs('social-media.inbox.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.inbox.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="inbox" class="w-3.5 h-3.5 {{ request()->routeIs('social-media.inbox.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Kotak Masuk Pesan</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu Melayang (Collapsed Flyout) --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'marketing'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Pelanggan &amp; Pemasaran
                        </div>
                        @if (\App\Support\Context::hasPermission('customers.view'))
                            <a href="{{ route('customers.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="users" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Data Pelanggan</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('crm.view'))
                            <a href="{{ route('crm.members.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="award" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Member &amp; Loyalitas</span>
                            </a>
                            <a href="{{ route('crm.vouchers.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="ticket" class="w-3.5 h-3.5 text-[#FF2D55]"></i>
                                <span>Voucher Diskon Promosi</span>
                            </a>
                        @endif
                        @if ($canAccessStorefront)
                            <a href="{{ route('storefront.orders.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Pesanan Toko Online</span>
                            </a>
                            <a href="{{ route('landing-page.edit') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="globe" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Landing Page Usaha</span>
                            </a>
                            <a href="{{ route('landing-page.popup.edit') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="megaphone" class="w-3.5 h-3.5 text-[#FF2D55]"></i>
                                <span>Pop Up Promo</span>
                            </a>
                            @if ($sidebarShowReservation)
                                <a href="{{ route('storefront.reservations.index') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Reservasi Meja</span>
                                </a>
                            @endif
                            @if ($sidebarShowShipping)
                                <a href="{{ route('storefront.shipping.index') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="truck" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                    <span>Pengaturan Ongkir</span>
                                </a>
                            @endif
                            <a href="{{ route('storefront.settings.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="store" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Pengaturan Toko Online</span>
                            </a>
                        @endif
                        @if ($canAccessChannels)
                            <a href="{{ route('whatsapp.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="message-square" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>WhatsApp Bisnis</span>
                            </a>
                            <a href="{{ route('whatsapp.broadcast.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="send" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Broadcast Pesan WA</span>
                            </a>
                            <a href="{{ route('whatsapp.logs.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="list" class="w-3.5 h-3.5 text-[#8E8E93]"></i>
                                <span>Riwayat Pesan WA</span>
                            </a>
                            <a href="{{ route('social-media.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="share-2" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Media Sosial Omnichannel</span>
                            </a>
                            <a href="{{ route('social-media.posts.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Konten &amp; Jadwal</span>
                            </a>
                            <a href="{{ route('social-media.calendar') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Kalender Konten</span>
                            </a>
                            <a href="{{ route('social-media.inbox.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="inbox" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Kotak Masuk Pesan</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 6: KEUANGAN & BIAYA POKOK                          --}}
        {{-- ======================================================== --}}
        @if ($canAccessFinance || $canAccessCosting || $canAccessHrm)
            <div class="space-y-1 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                    Keuangan &amp; Biaya
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'finance'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-group-finance" data-tour-group="finance"
                        @click="financeOpen = !financeOpen" role="button"
                        :aria-expanded="financeOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Keuangan & Biaya' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isFinanceRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="wallet"
                                class="w-4 h-4 {{ $isFinanceRoute ? 'text-[#34C759]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Keuangan &amp; Biaya</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="financeOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    {{-- Submenu Terbuka (Expanded) --}}
                    <div x-show="financeOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        @if ($canAccessFinance)
                            {{-- 1. Keuangan Sederhana (UMKM Default) --}}
                            <a href="{{ route('finance.cash-bank.index') }}" id="tour-nav-cash-bank"
                                {{ request()->routeIs('finance.cash-bank.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.cash-bank.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="landmark" class="w-3.5 h-3.5 {{ request()->routeIs('finance.cash-bank.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Kas &amp; Rekening Bank</span>
                            </a>

                            <a href="{{ route('finance.expenses.index') }}" id="tour-nav-expenses"
                                {{ request()->routeIs('finance.expenses.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.expenses.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 {{ request()->routeIs('finance.expenses.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Pengeluaran Operasional</span>
                            </a>

                            <a href="{{ route('finance.receivables') }}"
                                {{ request()->routeIs('finance.receivables') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.receivables') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="arrow-down-left" class="w-3.5 h-3.5 {{ request()->routeIs('finance.receivables') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Daftar Piutang Usaha</span>
                            </a>

                            <a href="{{ route('finance.payables') }}"
                                {{ request()->routeIs('finance.payables') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.payables') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 {{ request()->routeIs('finance.payables') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Daftar Utang Usaha</span>
                            </a>

                            <a href="{{ route('finance.settlements.index') }}"
                                {{ request()->routeIs('finance.settlements.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.settlements.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="badge-check" class="w-3.5 h-3.5 {{ request()->routeIs('finance.settlements.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Pencairan Dana Penjualan</span>
                            </a>

                            {{-- 2. Akuntansi Lengkap Korporasi (Dapat diaktifkan di Tab Kelola Modul) --}}
                            @php
                                $showCorporateAccounting = $sidebarBiz ? $sidebarBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_ACCOUNTING_CORPORATE) : true;
                            @endphp

                            @if ($showCorporateAccounting && \App\Support\Context::hasPermission('accounting.view'))
                                <div class="pt-2 pb-0.5 px-2 text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                                    Akuntansi Korporasi
                                </div>

                                <a href="{{ route('finance.coa.index') }}"
                                    {{ request()->routeIs('finance.coa.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.coa.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="list-tree" class="w-3.5 h-3.5 {{ request()->routeIs('finance.coa.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Bagan Akun Keuangan (COA)</span>
                                </a>

                                <a href="{{ route('finance.journals.index') }}"
                                    {{ request()->routeIs('finance.journals.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.journals.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 {{ request()->routeIs('finance.journals.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Buku Jurnal Keuangan</span>
                                </a>

                                <a href="{{ route('finance.general-ledger') }}"
                                    {{ request()->routeIs('finance.general-ledger') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.general-ledger') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="book-open" class="w-3.5 h-3.5 {{ request()->routeIs('finance.general-ledger') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Buku Besar Akun</span>
                                </a>

                                <a href="{{ route('finance.balance-sheet') }}"
                                    {{ request()->routeIs('finance.balance-sheet') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.balance-sheet') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="scale" class="w-3.5 h-3.5 {{ request()->routeIs('finance.balance-sheet') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Neraca Keuangan SAK EMKM</span>
                                </a>

                                <a href="{{ route('finance.trial-balance') }}"
                                    {{ request()->routeIs('finance.trial-balance') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.trial-balance') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="table-properties" class="w-3.5 h-3.5 {{ request()->routeIs('finance.trial-balance') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Neraca Saldo</span>
                                </a>

                                <a href="{{ route('finance.reconciliations.index') }}"
                                    {{ request()->routeIs('finance.reconciliations.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.reconciliations.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="git-compare" class="w-3.5 h-3.5 {{ request()->routeIs('finance.reconciliations.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Rekonsiliasi Bank</span>
                                </a>
                            @endif
                        @endif

                        @if ($canAccessCosting)
                            <div class="pt-1.5 pb-0.5 px-2 text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                                Kalkulasi HPP &amp; Biaya
                            </div>

                            <a href="{{ route('calculator.index') }}" id="tour-nav-calculator"
                                {{ request()->routeIs('calculator.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('calculator.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="calculator" class="w-3.5 h-3.5 {{ request()->routeIs('calculator.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Kalkulator HPP &amp; Margin</span>
                            </a>

                            <a href="{{ route('simulator.index') }}" id="tour-nav-simulator"
                                {{ request()->routeIs('simulator.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('simulator.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="cpu" class="w-3.5 h-3.5 {{ request()->routeIs('simulator.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Simulator Harga Jual</span>
                            </a>

                            <a href="{{ route('labor-machines.index') }}" id="tour-nav-labor-machines"
                                {{ request()->routeIs('labor-machines.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('labor-machines.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="hammer" class="w-3.5 h-3.5 {{ request()->routeIs('labor-machines.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Biaya Mesin &amp; Tenaga Kerja</span>
                            </a>

                            <a href="{{ route('profitability.index') }}" id="tour-nav-profitability"
                                {{ request()->routeIs('profitability.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('profitability.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="pie-chart" class="w-3.5 h-3.5 {{ request()->routeIs('profitability.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Analisis Margin &amp; BEP</span>
                            </a>
                        @endif

                        @if ($canAccessHrm)
                            <div class="pt-2 pb-0.5 px-2 text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 border-t border-black/5 dark:border-white/10 mt-1.5">
                                Karyawan &amp; Payroll
                            </div>

                            <a href="{{ route('hrm.index') }}"
                                {{ request()->routeIs('hrm.index') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('hrm.index') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="users-2" class="w-3.5 h-3.5 {{ request()->routeIs('hrm.index') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Data Karyawan &amp; Staf</span>
                            </a>

                            <a href="{{ route('hrm.payrolls.index') }}"
                                {{ request()->routeIs('hrm.payrolls.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('hrm.payrolls.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="badge-percent" class="w-3.5 h-3.5 {{ request()->routeIs('hrm.payrolls.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Payroll &amp; Slip Gaji</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu Melayang (Collapsed Flyout) --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'finance'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Keuangan &amp; Biaya
                        </div>
                        @if ($canAccessFinance)
                            <a href="{{ route('finance.cash-bank.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="landmark" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Kas &amp; Rekening Bank</span>
                            </a>
                            <a href="{{ route('finance.expenses.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-[#FF3B30]"></i>
                                <span>Pengeluaran Operasional</span>
                            </a>
                            <a href="{{ route('finance.receivables') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="arrow-down-left" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Daftar Piutang Usaha</span>
                            </a>
                            <a href="{{ route('finance.payables') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Daftar Utang Usaha</span>
                            </a>
                            <a href="{{ route('finance.settlements.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="badge-check" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Pencairan Dana Penjualan</span>
                            </a>

                            @if ($showCorporateAccounting && \App\Support\Context::hasPermission('accounting.view'))
                                <div class="px-2.5 pt-1.5 pb-0.5 text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 border-t border-black/5 dark:border-white/10 mt-1">
                                    Akuntansi Korporasi
                                </div>
                                <a href="{{ route('finance.coa.index') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="list-tree" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Bagan Akun Keuangan (COA)</span>
                                </a>
                                <a href="{{ route('finance.journals.index') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                    <span>Buku Jurnal Keuangan</span>
                                </a>
                                <a href="{{ route('finance.general-ledger') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="book-open" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>Buku Besar Akun</span>
                                </a>
                                <a href="{{ route('finance.balance-sheet') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="scale" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Neraca Keuangan</span>
                                </a>
                                <a href="{{ route('finance.trial-balance') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="table-properties" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                    <span>Neraca Saldo</span>
                                </a>
                                <a href="{{ route('finance.reconciliations.index') }}"
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                    <i data-lucide="git-compare" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                    <span>Rekonsiliasi Bank</span>
                                </a>
                            @endif
                        @endif
                        @if ($canAccessCosting)
                            <a href="{{ route('calculator.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="calculator" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Kalkulator HPP &amp; Margin</span>
                            </a>
                            <a href="{{ route('simulator.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="cpu" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Simulator Harga Jual</span>
                            </a>
                            <a href="{{ route('labor-machines.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="hammer" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Biaya Mesin &amp; Tenaga Kerja</span>
                            </a>
                            <a href="{{ route('profitability.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="pie-chart" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Analisis Margin &amp; BEP</span>
                            </a>
                        @endif
                        @if ($canAccessHrm)
                            <div class="px-2.5 pt-1.5 pb-0.5 text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 border-t border-black/5 dark:border-white/10 mt-1">
                                Karyawan &amp; Payroll
                            </div>
                            <a href="{{ route('hrm.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="users-2" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Data Karyawan &amp; Staf</span>
                            </a>
                            <a href="{{ route('hrm.payrolls.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="badge-percent" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Payroll &amp; Slip Gaji</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 7: LAPORAN & ANALITIK (REPORTS & ANALYTICS)         --}}
        {{-- ======================================================== --}}
        @if ($canAccessReports || $canAccessFinance)
            <div class="space-y-1 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                    Laporan &amp; Analitik
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'reports'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-group-reports" data-tour-group="reports"
                        @click="reportsOpen = !reportsOpen" role="button"
                        :aria-expanded="reportsOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Laporan & Analitik' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ (request()->routeIs('reports.*') || request()->routeIs('analytics.*') || request()->routeIs('pos.reports.*')) ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="bar-chart-3"
                                class="w-4 h-4 {{ (request()->routeIs('reports.*') || request()->routeIs('analytics.*') || request()->routeIs('pos.reports.*')) ? 'text-[#AF52DE]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Laporan &amp; Analitik</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="reportsOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    {{-- Submenu Terbuka (Expanded Accordion) --}}
                    <div x-show="reportsOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        @if ($canAccessReports)
                            <a href="{{ route('reports.index') }}" id="tour-nav-reports"
                                {{ request()->routeIs('reports.index') && !request()->query('tab') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('reports.index') && !request()->query('tab') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="bar-chart-3" class="w-3.5 h-3.5 {{ request()->routeIs('reports.index') && !request()->query('tab') ? 'text-white' : 'text-[#007AFF]' }} shrink-0"></i>
                                <span class="truncate">Pusat Laporan</span>
                            </a>

                            <a href="{{ route('analytics.index') }}" id="tour-nav-analytics"
                                {{ request()->routeIs('analytics.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('analytics.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="line-chart" class="w-3.5 h-3.5 {{ request()->routeIs('analytics.*') ? 'text-white' : 'text-[#AF52DE]' }} shrink-0"></i>
                                <span class="truncate">Analitik Bisnis &amp; Tren</span>
                            </a>

                            <a href="{{ route('reports.index', ['tab' => 'income_statement']) }}"
                                {{ request()->fullUrlIs('*tab=income_statement*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->fullUrlIs('*tab=income_statement*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="trending-up" class="w-3.5 h-3.5 {{ request()->fullUrlIs('*tab=income_statement*') ? 'text-white' : 'text-[#34C759]' }} shrink-0"></i>
                                <span class="truncate">Laba Rugi (Profit &amp; Loss)</span>
                            </a>

                            <a href="{{ route('reports.index', ['tab' => 'cash_flow']) }}"
                                {{ request()->fullUrlIs('*tab=cash_flow*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->fullUrlIs('*tab=cash_flow*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="activity" class="w-3.5 h-3.5 {{ request()->fullUrlIs('*tab=cash_flow*') ? 'text-white' : 'text-[#007AFF]' }} shrink-0"></i>
                                <span class="truncate">Arus Kas (Cash Flow)</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('pos.reports'))
                            <a href="{{ route('pos.reports.index') }}" id="tour-nav-pos-reports"
                                {{ request()->routeIs('pos.reports.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.reports.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 {{ request()->routeIs('pos.reports.*') ? 'text-white' : 'text-[#FF9500]' }} shrink-0"></i>
                                <span class="truncate">Laporan Penjualan Kasir</span>
                            </a>
                        @endif

                        @if ($canAccessReports)
                            <a href="{{ route('reports.index', ['tab' => 'stock']) }}"
                                {{ request()->fullUrlIs('*tab=stock*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->fullUrlIs('*tab=stock*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="package" class="w-3.5 h-3.5 {{ request()->fullUrlIs('*tab=stock*') ? 'text-white' : 'text-[#AF52DE]' }} shrink-0"></i>
                                <span class="truncate">Laporan &amp; Valuasi Stok</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('reports.view') || \App\Support\Context::isOwner() || $canAccessFinance)
                            <a href="{{ route('tax.index') }}" id="tour-nav-tax"
                                {{ request()->routeIs('tax.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('tax.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="percent" class="w-3.5 h-3.5 {{ request()->routeIs('tax.*') ? 'text-white' : 'text-[#FF2D55]' }} shrink-0"></i>
                                <span class="truncate">Laporan Pajak &amp; Kepatuhan</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu Melayang (Collapsed Flyout) --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'reports'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Laporan &amp; Analitik
                        </div>
                        @if ($canAccessReports)
                            <a href="{{ route('reports.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="bar-chart-3" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Pusat Laporan</span>
                            </a>
                            <a href="{{ route('analytics.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="line-chart" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                <span>Analitik Bisnis &amp; Tren</span>
                            </a>
                            <a href="{{ route('reports.index', ['tab' => 'income_statement']) }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="trending-up" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Laba Rugi (Profit &amp; Loss)</span>
                            </a>
                            <a href="{{ route('reports.index', ['tab' => 'cash_flow']) }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="activity" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Arus Kas (Cash Flow)</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('pos.reports'))
                            <a href="{{ route('pos.reports.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Laporan Penjualan Kasir</span>
                            </a>
                        @endif
                        @if ($canAccessReports)
                            <a href="{{ route('reports.index', ['tab' => 'stock']) }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="package" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                <span>Laporan &amp; Valuasi Stok</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('reports.view') || \App\Support\Context::isOwner() || $canAccessFinance)
                            <a href="{{ route('tax.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="percent" class="w-3.5 h-3.5 text-[#FF2D55]"></i>
                                <span>Laporan Pajak &amp; Kepatuhan</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- KOMUNITAS OWNER (EXCLUSIVE TOP-LEVEL PORTAL)             --}}
        {{-- ======================================================== --}}
        @if (\App\Support\Context::isOwner())
            <div class="pt-2 pb-1">
                <a href="#" id="tour-nav-community"
                    @click.prevent="openComingSoon({ title: 'Komunitas Owner UMKM', icon: 'users-round', desc: 'Forum diskusi eksklusif bagi para pemilik usaha UMKM di Cooca untuk saling berbagi strategi, networking, dan berkembang bersama.', color: 'amber' })"
                    {{ request()->routeIs('community.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Komunitas Owner UMKM' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('community.*') ? 'bg-[#FF9500] text-white shadow-[0_2px_8px_rgba(255,149,0,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-[#FF9500]/8 dark:hover:bg-[#FF9500]/15 hover:text-[#FF9500] dark:hover:text-[#FF9F0A]' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('community.*') ? 'bg-white/20 text-white' : 'bg-[#FF9500]/15 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="users-round" class="w-3.5 h-3.5"></i>
                    </span>
                    <div class="flex items-center justify-between flex-1 min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                        <span class="truncate tracking-tight">Komunitas Owner</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] shrink-0">Eksklusif</span>
                    </div>
                </a>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 8: PENGATURAN USAHA                                --}}
        {{-- ======================================================== --}}
        @if ($canAccessSettings || $canAccessRoles || $canAccessBilling || \App\Support\Context::isOwner())
            <div class="space-y-1 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                    Pengaturan Usaha
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'settings'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-group-settings" data-tour-group="settings"
                        @click="settingsOpen = !settingsOpen" role="button"
                        :aria-expanded="settingsOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Pengaturan Usaha' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isSettingsRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="settings"
                                class="w-4 h-4 {{ $isSettingsRoute ? 'text-[#8E8E93]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Pengaturan Usaha</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="settingsOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    {{-- Submenu Terbuka (Expanded) --}}
                    <div x-show="settingsOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        <a href="{{ route('profile.edit') }}"
                            {{ request()->routeIs('profile.edit') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('profile.edit') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="user" class="w-3.5 h-3.5 {{ request()->routeIs('profile.edit') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">Profil Pengguna</span>
                        </a>

                        @if ($canAccessSettings)
                            <a href="{{ route('settings.index') }}" id="tour-nav-settings"
                                {{ (request()->routeIs('settings.*') && !request()->routeIs('settings.audit-logs.*') && !request()->routeIs('settings.approval-rules.*') && !request()->routeIs('settings.roles.*') && !request()->routeIs('settings.pos.printers.*')) ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ (request()->routeIs('settings.*') && !request()->routeIs('settings.audit-logs.*') && !request()->routeIs('settings.approval-rules.*') && !request()->routeIs('settings.roles.*') && !request()->routeIs('settings.pos.printers.*')) ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="sliders" class="w-3.5 h-3.5 {{ (request()->routeIs('settings.*') && !request()->routeIs('settings.audit-logs.*') && !request()->routeIs('settings.approval-rules.*') && !request()->routeIs('settings.roles.*') && !request()->routeIs('settings.pos.printers.*')) ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Pengaturan Usaha &amp; Cabang</span>
                            </a>
                        @endif

                        @if ($canAccessSettings || \App\Support\Context::isOwner())
                            <a href="{{ route('approval-rules.index') }}" id="tour-nav-approval-rules"
                                {{ request()->routeIs('approval-rules.*') || request()->routeIs('settings.approval-rules.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('approval-rules.*') || request()->routeIs('settings.approval-rules.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 {{ request()->routeIs('approval-rules.*') || request()->routeIs('settings.approval-rules.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Aturan Persetujuan Transaksi (MAR)</span>
                            </a>
                        @endif

                        @if ($canAccessAuditLogs)
                            <a href="{{ route('settings.audit-logs.index') }}" id="tour-nav-audit-logs"
                                {{ request()->routeIs('settings.audit-logs.*') || request()->routeIs('audit-logs.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center justify-between px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('settings.audit-logs.*') || request()->routeIs('audit-logs.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <div class="flex items-center gap-2 min-w-0">
                                    <i data-lucide="shield-alert" class="w-3.5 h-3.5 {{ request()->routeIs('settings.audit-logs.*') || request()->routeIs('audit-logs.*') ? 'text-white' : 'text-[#FF3B30]' }} shrink-0"></i>
                                    <span class="truncate">Jejak Audit &amp; Anti-Fraud</span>
                                </div>
                                @if (isset($recentHighRiskCount) && $recentHighRiskCount > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ request()->routeIs('settings.audit-logs.*') || request()->routeIs('audit-logs.*') ? 'bg-white/20 text-white border-white/30' : 'bg-[#FF3B30]/15 text-[#FF3B30] border-[#FF3B30]/30' }} border shrink-0">
                                        {{ $recentHighRiskCount }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        @if ($canAccessRoles)
                            <a href="{{ route('roles.index') }}"
                                {{ request()->routeIs('roles.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('roles.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="shield" class="w-3.5 h-3.5 {{ request()->routeIs('roles.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Izin Akses &amp; Peran Karyawan</span>
                            </a>
                        @endif

                        @if ($canAccessBilling)
                            <a href="{{ route('billing.limits') }}" id="tour-nav-billing"
                                {{ request()->routeIs('billing.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('billing.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 {{ request()->routeIs('billing.*') ? 'text-white' : 'text-[#FF9500]' }} shrink-0"></i>
                                <span class="truncate">Paket Berlangganan &amp; Kuota</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::isOwner())
                            <a href="{{ route('feedback.bugs.index') }}"
                                {{ request()->routeIs('feedback.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('feedback.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="life-buoy" class="w-3.5 h-3.5 {{ request()->routeIs('feedback.*') ? 'text-white' : 'text-[#FF9500]' }} shrink-0"></i>
                                <span class="truncate">Bantuan &amp; Dukungan</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu Melayang (Collapsed Flyout) --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'settings'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Pengaturan Usaha
                        </div>
                        <a href="{{ route('profile.edit') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>Profil Pengguna</span>
                        </a>
                        @if ($canAccessSettings)
                            <a href="{{ route('settings.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="sliders" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Pengaturan Usaha &amp; Cabang</span>
                            </a>
                        @endif
                        @if ($canAccessSettings || \App\Support\Context::isOwner())
                            <a href="{{ route('approval-rules.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Aturan Persetujuan Transaksi (MAR)</span>
                            </a>
                        @endif
                        @if ($canAccessAuditLogs)
                            <a href="{{ route('settings.audit-logs.index') }}"
                                class="sidebar-item flex items-center justify-between px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <div class="flex items-center gap-2 min-w-0">
                                    <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-[#FF3B30] shrink-0"></i>
                                    <span>Jejak Audit &amp; Anti-Fraud</span>
                                </div>
                                @if (isset($recentHighRiskCount) && $recentHighRiskCount > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] border border-[#FF3B30]/30 shrink-0">
                                        {{ $recentHighRiskCount }}
                                    </span>
                                @endif
                            </a>
                        @endif
                        @if ($canAccessRoles)
                            <a href="{{ route('roles.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="shield" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Izin Akses &amp; Peran Karyawan</span>
                            </a>
                        @endif
                        @if ($canAccessBilling)
                            <a href="{{ route('billing.limits') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Paket Berlangganan &amp; Kuota</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::isOwner())
                            <a href="{{ route('feedback.bugs.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="life-buoy" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Bantuan &amp; Dukungan</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </nav>

    @if ($canAccessBilling)
        {{-- 4. Subscription Plan Card (Apple HIG Special Design Card) --}}
        <div
            class="sidebar-bottom p-2.5 border-t border-black/5 dark:border-white/10 shrink-0 bg-white/75 dark:bg-[#1C1C1E]/75 backdrop-blur-md">
            <!-- Collapsed Sidebar: Compact Squircle Action -->
            <div x-show="sidebarCollapsed" class="sidebar-plan-collapsed flex justify-center py-1">
                @if ($isCorePlan)
                    <a href="{{ route('billing.limits') }}" title="Cooca (∞ Unlimited) - Kelola Kuota"
                        class="w-10 h-10 rounded-[10px] bg-[#34C759]/12 border border-[#34C759]/30 text-[#34C759] dark:text-[#30D158] flex items-center justify-center hover:bg-[#34C759]/20 active:scale-95 transition-all shadow-2xs">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </a>
                @else
                    <a href="{{ route('billing.patungan') }}" title="Paket Free (Solo) - Tingkatkan ke Cooca"
                        class="w-10 h-10 rounded-[10px] bg-gradient-to-br from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center hover:opacity-90 active:scale-95 transition-all shadow-[0_2px_8px_rgba(0,122,255,0.3)]">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </a>
                @endif
            </div>

            <!-- Expanded Sidebar: Rich Apple HIG Card -->
            <div x-show="!sidebarCollapsed" class="sidebar-plan-expanded" x-transition.opacity>
                @if ($isCorePlan)
                    {{-- Active Core Plan Card --}}
                    <div
                        class="relative overflow-hidden rounded-[12px] p-3 border border-[#34C759]/25 bg-gradient-to-br from-[#34C759]/10 via-[#30D158]/5 to-transparent dark:from-[#30D158]/15 dark:via-[#30D158]/5 dark:to-transparent space-y-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                        <div class="flex items-center justify-between gap-1.5">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse shrink-0"></span>
                                <span class="text-[12px] font-semibold text-black dark:text-white truncate">Cooca</span>
                            </div>
                            <span
                                class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] border border-[#34C759]/25 shrink-0">
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
                            <div class="flex items-center justify-between text-[11px]">
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
                    <div
                        class="relative overflow-hidden rounded-[12px] p-3 border border-black/5 dark:border-white/10 bg-gradient-to-br from-[#007AFF]/10 via-[#5856D6]/5 to-transparent dark:from-[#007AFF]/15 dark:via-[#5856D6]/10 dark:to-transparent space-y-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.03)]">
                        {{-- Header Badge & Title --}}
                        <div class="flex items-center justify-between gap-1.5">
                            <span class="text-[12px] font-semibold text-black dark:text-white truncate">Paket Free
                                (Solo)</span>
                            <span
                                class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A] border border-[#FF9500]/25 shrink-0">
                                Free
                            </span>
                        </div>

                        {{-- Usage Meters --}}
                        <div class="space-y-1.5 text-[11px]">
                            {{-- Katalog Produk --}}
                            <div class="space-y-0.5">
                                <div
                                    class="flex items-center justify-between text-[11px] text-black/60 dark:text-white/60">
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
                                <div
                                    class="flex items-center justify-between text-[11px] text-black/60 dark:text-white/60">
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
                                <div
                                    class="flex items-center justify-between text-[11px] text-black/60 dark:text-white/60">
                                    <span>Invoice Bulan Ini:</span>
                                    <span class="font-medium text-black/80 dark:text-white/80 tabular-nums">
                                        {{ $navUsage['invoices_this_month']['used'] ?? 0 }} / 10
                                    </span>
                                </div>
                                <div class="w-full h-1 bg-black/5 dark:bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#AF52DE] rounded-full transition-all duration-300"
                                        style="width: {{ min(100, $navUsage['invoices_this_month']['percent'] ?? 0) }}%">
                                    </div>
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
                                class="block text-center text-[11px] text-black/55 dark:text-white/55 hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition">
                                Tingkatkan ke Cooca ›
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</aside>
