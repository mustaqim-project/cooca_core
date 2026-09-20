@php
    $activeBiz = \App\Support\Context::business();
    $navEntitlement = app(\App\Domain\Billing\EntitlementService::class);
    $navUsage = $activeBiz ? $navEntitlement->getUsageSummary($activeBiz) : null;
    $isCorePlan = $navUsage['is_core'] ?? false;

    $isSalesRoute =
        request()->routeIs('sales.*') ||
        request()->routeIs('invoices.*') ||
        request()->routeIs('customers.*') ||
        request()->routeIs('crm.*');
    $isPosRoute = request()->routeIs('pos.*');
    $isPurchasingRoute =
        request()->routeIs('purchasing.*') ||
        request()->routeIs('purchase-orders.*') ||
        request()->routeIs('purchase.returns.*') ||
        request()->routeIs('suppliers.*');
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
        request()->routeIs('pos.modifiers.*');
    $isFinanceRoute =
        request()->routeIs('finance.*') ||
        request()->routeIs('calculator.*') ||
        request()->routeIs('labor-machines.*') ||
        request()->routeIs('profitability.*') ||
        request()->routeIs('simulator.*') ||
        request()->routeIs('reports.*') ||
        request()->routeIs('pos.reports.*') ||
        request()->routeIs('hrm.*') ||
        request()->routeIs('tax.*');
    $isChannelsRoute =
        request()->routeIs('storefront.*') ||
        request()->routeIs('landing-page.*') ||
        request()->routeIs('whatsapp.*') ||
        request()->routeIs('social-media.*');
    $isSettingsRoute =
        request()->routeIs('settings.*') ||
        request()->routeIs('roles.*') ||
        request()->routeIs('billing.*') ||
        request()->routeIs('community.*') ||
        request()->routeIs('feedback.*') ||
        request()->routeIs('profile.*');

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
        \App\Support\Context::hasPermission('whatsapp.manage') ||
        \App\Support\Context::isOwner();

    $canAccessStorefront =
        \App\Support\Context::hasPermission('storefront.orders.view') ||
        \App\Support\Context::hasPermission('storefront.manage') ||
        \App\Support\Context::hasPermission('storefront.shipping.manage') ||
        \App\Support\Context::hasPermission('storefront.reservations.manage') ||
        \App\Support\Context::hasPermission('storefront.po.manage') ||
        \App\Support\Context::hasPermission('cms.manage') ||
        \App\Support\Context::isOwner();

    $canAccessSettings = \App\Support\Context::hasPermission('settings.view') || \App\Support\Context::isOwner();
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
        class="sidebar-nav flex-1 overflow-y-auto px-2.5 py-3 space-y-2.5 min-h-0 overscroll-contain text-xs"
        x-data="{
            salesOpen: {{ $isSalesRoute ? 'true' : 'false' }},
            inventoryOpen: {{ $isInventoryRoute ? 'true' : 'false' }},
            purchasingOpen: {{ $isPurchasingRoute ? 'true' : 'false' }},
            financeOpen: {{ $isFinanceRoute ? 'true' : 'false' }},
            channelsOpen: {{ $isChannelsRoute ? 'true' : 'false' }},
            settingsOpen: {{ $isSettingsRoute ? 'true' : 'false' }}
        }">

        {{-- ======================================================== --}}
        {{-- GRUP 1: BERANDA & KASIR (Ringkasan & Operasional Utama)  --}}
        {{-- ======================================================== --}}
        <div class="space-y-0.5">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-semibold tracking-wide uppercase text-black/55 dark:text-white/55 select-none">
                Beranda &amp; Kasir
            </div>

            {{-- Beranda Dashboard --}}
            <a href="{{ route('dashboard') }}" id="tour-nav-dashboard"
                {{ request()->routeIs('dashboard') ? 'aria-current="page"' : '' }}
                :title="sidebarCollapsed ? 'Beranda' : ''"
                class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] {{ request()->routeIs('dashboard') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                <i data-lucide="layout-dashboard"
                    class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Beranda</span>
            </a>

            {{-- Buka Kasir POS --}}
            @if (\App\Support\Context::hasPermission('pos.terminal'))
                <a href="{{ route('pos.terminal') }}" id="tour-nav-pos-terminal"
                    {{ request()->routeIs('pos.terminal') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Buka Kasir POS' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] {{ request()->routeIs('pos.terminal') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="calculator"
                        class="w-4 h-4 {{ request()->routeIs('pos.terminal') ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Buka Kasir POS</span>
                </a>
            @endif

            {{-- Transaksi Kasir & Shift --}}
            @if (
                \App\Support\Context::hasPermission('pos.terminal') ||
                    \App\Support\Context::hasPermission('pos.reports') ||
                    \App\Support\Context::hasPermission('pos.orders'))
                <a href="{{ route('pos.orders.index') }}" id="tour-nav-pos-orders"
                    {{ request()->routeIs('pos.orders.*') || request()->routeIs('pos.shifts.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Transaksi Kasir' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] {{ request()->routeIs('pos.orders.*') || request()->routeIs('pos.shifts.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="receipt"
                        class="w-4 h-4 {{ request()->routeIs('pos.orders.*') || request()->routeIs('pos.shifts.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Transaksi Kasir</span>
                </a>
            @endif

            {{-- Layar Dapur (KDS) --}}
            @if (\App\Support\Context::hasPermission('pos.kitchen'))
                <a href="{{ route('pos.kitchen.index') }}" id="tour-nav-pos-kitchen"
                    {{ request()->routeIs('pos.kitchen.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Layar Dapur' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] {{ request()->routeIs('pos.kitchen.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="chef-hat"
                        class="w-4 h-4 {{ request()->routeIs('pos.kitchen.*') ? 'text-white' : 'text-[#FF9500] dark:text-[#FF9F0A]' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Layar Dapur</span>
                </a>
            @endif

            {{-- Meja & QR Resto --}}
            @if (\App\Support\Context::hasPermission('pos.tables'))
                <a href="{{ route('pos.tables.index') }}" id="tour-nav-pos-tables"
                    {{ request()->routeIs('pos.tables.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Meja & QR Resto' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] {{ request()->routeIs('pos.tables.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="layout-grid"
                        class="w-4 h-4 {{ request()->routeIs('pos.tables.*') ? 'text-white' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Meja &amp; QR Resto</span>
                </a>
            @endif

            {{-- Asisten AI Cooca --}}
            @if (\App\Support\Context::hasPermission('ai.access'))
                <a href="{{ route('pos.ai.index') }}" id="tour-nav-ai"
                    {{ request()->routeIs('pos.ai.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Asisten AI' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] {{ request()->routeIs('pos.ai.*') ? 'bg-[#AF52DE] text-white shadow-[0_1px_2px_rgba(175,82,222,0.25)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="bot"
                        class="w-4 h-4 {{ request()->routeIs('pos.ai.*') ? 'text-white' : 'text-[#AF52DE] dark:text-[#BF5AF2]' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Asisten AI</span>
                </a>
            @endif

            {{-- Komunitas Owner UMKM --}}
            @if (\App\Support\Context::isOwner())
                <a href="#" id="tour-nav-community"
                    @click.prevent="openComingSoon({ title: 'Komunitas Owner UMKM', icon: 'users', desc: 'Forum diskusi eksklusif bagi para pemilik usaha UMKM di Cooca untuk saling berbagi strategi dan berkembang bersama.', color: 'amber' })"
                    {{ request()->routeIs('community.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Komunitas Owner (Segera)' : ''"
                    class="sidebar-item flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-all active:scale-[0.97] {{ request()->routeIs('community.*') ? 'bg-[#FF9500] text-white shadow-[0_1px_2px_rgba(255,149,0,0.25)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="users"
                        class="w-4 h-4 {{ request()->routeIs('community.*') ? 'text-white' : 'text-[#FF9500] dark:text-[#FF9F0A]' }} shrink-0"></i>
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Komunitas Owner</span>
                    <span x-show="!sidebarCollapsed"
                        class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] font-semibold shrink-0">Segera</span>
                </a>
            @endif
        </div>

        {{-- ======================================================== --}}
        {{-- GRUP 2: PENJUALAN & PELANGGAN                            --}}
        {{-- ======================================================== --}}
        @if ($canAccessB2bSales || $canAccessCrm)
            <div class="space-y-0.5 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-semibold tracking-wide uppercase text-black/55 dark:text-white/55 select-none">
                    Penjualan &amp; Pelanggan
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group" x-data="{ flyoutOpen: false }"
                    @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                    <button type="button" id="tour-group-sales" data-tour-group="sales"
                        @click="salesOpen = !salesOpen" role="button"
                        :aria-expanded="salesOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Penjualan & Pelanggan' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isSalesRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="shopping-cart"
                                class="w-4 h-4 {{ $isSalesRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Penjualan &amp; Pelanggan</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="salesOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    {{-- Submenu Terbuka (Expanded) --}}
                    <div x-show="salesOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        @if (\App\Support\Context::hasPermission('sales.view'))
                            <a href="{{ route('sales.orders.index') }}" id="tour-nav-sales-orders"
                                {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') && !request()->routeIs('sales.quotations.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') && !request()->routeIs('sales.quotations.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="check-square" class="w-3.5 h-3.5 {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') && !request()->routeIs('sales.quotations.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Pesanan Penjualan</span>
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
                                <span class="truncate">Faktur &amp; Piutang</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('sales.view') || \App\Support\Context::hasPermission('sales.returns'))
                            <a href="{{ route('sales.returns.index') }}" id="tour-nav-sales-returns"
                                {{ request()->routeIs('sales.returns.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('sales.returns.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="undo-2" class="w-3.5 h-3.5 {{ request()->routeIs('sales.returns.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Retur Penjualan</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('customers.view') || \App\Support\Context::hasPermission('crm.view'))
                            <div class="px-2 pt-2 pb-0.5 text-[11px] font-bold tracking-wider uppercase text-black/55 dark:text-white/55 select-none">
                                Pelanggan &amp; Member
                            </div>

                            @if (\App\Support\Context::hasPermission('customers.view'))
                                <a href="{{ route('customers.index') }}" id="tour-nav-customers"
                                    {{ request()->routeIs('customers.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('customers.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="users" class="w-3.5 h-3.5 {{ request()->routeIs('customers.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Pelanggan</span>
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
                        @endif
                    </div>

                    {{-- Submenu Melayang (Collapsed Flyout) --}}
                    <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Penjualan &amp; Pelanggan
                        </div>
                        @if (\App\Support\Context::hasPermission('sales.view'))
                            <a href="{{ route('sales.orders.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="check-square" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Pesanan Penjualan</span>
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
                                <span>Faktur &amp; Piutang</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('sales.view') || \App\Support\Context::hasPermission('sales.returns'))
                            <a href="{{ route('sales.returns.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="undo-2" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Retur Penjualan</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('customers.view'))
                            <a href="{{ route('customers.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="users" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Pelanggan</span>
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
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 3: PRODUK & PERSEDIAAN                              --}}
        {{-- ======================================================== --}}
        @if ($canAccessInventory || $canAccessMasterData)
            <div class="space-y-0.5 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-semibold tracking-wide uppercase text-black/55 dark:text-white/55 select-none">
                    Produk &amp; Persediaan
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group" x-data="{ flyoutOpen: false }"
                    @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                    <button type="button" id="tour-group-inventory" data-tour-group="inventory"
                        @click="inventoryOpen = !inventoryOpen" role="button"
                        :aria-expanded="inventoryOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Produk & Persediaan' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isInventoryRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="box"
                                class="w-4 h-4 {{ $isInventoryRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Produk &amp; Persediaan</span>
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
                                {{ request()->routeIs('products.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('products.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="package" class="w-3.5 h-3.5 {{ request()->routeIs('products.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Katalog Produk &amp; Menu</span>
                            </a>

                            <a href="{{ route('services.index') }}" id="tour-nav-services"
                                {{ request()->routeIs('services.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('services.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="wrench" class="w-3.5 h-3.5 {{ request()->routeIs('services.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Jasa &amp; Layanan</span>
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

                        @if (\App\Support\Context::hasPermission('pos.modifiers'))
                            <a href="{{ route('pos.modifiers.index') }}" id="tour-nav-product-modifiers"
                                {{ request()->routeIs('pos.modifiers.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.modifiers.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5 {{ request()->routeIs('pos.modifiers.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Varian &amp; Opsi Tambahan</span>
                            </a>
                        @endif

                        {{-- Subgroup: Stok Gudang --}}
                        @if (\App\Support\Context::hasPermission('inventory.view') || \App\Support\Context::hasPermission('warehouse.view'))
                            <div class="px-2 pt-2 pb-0.5 text-[11px] font-bold tracking-wider uppercase text-black/55 dark:text-white/55 select-none">
                                Stok &amp; Gudang
                            </div>

                            @if (\App\Support\Context::hasPermission('inventory.view'))
                                <a href="{{ route('inventory.stocks') }}" id="tour-nav-inventory-stocks"
                                    {{ request()->routeIs('inventory.stocks') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inventory.stocks') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="boxes" class="w-3.5 h-3.5 {{ request()->routeIs('inventory.stocks') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Stok Gudang</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('warehouse.view'))
                                <a href="{{ route('warehouse.index') }}" id="tour-nav-warehouse"
                                    {{ request()->routeIs('warehouse.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('warehouse.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="warehouse" class="w-3.5 h-3.5 {{ request()->routeIs('warehouse.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Lokasi Gudang</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('inventory.manage'))
                                <a href="{{ route('inventory.opnames.index') }}" id="tour-nav-inventory-opnames"
                                    {{ request()->routeIs('inventory.opnames.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inventory.opnames.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="clipboard-check" class="w-3.5 h-3.5 {{ request()->routeIs('inventory.opnames.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Opname Stok Fisik</span>
                                </a>

                                <a href="{{ route('inventory.transfers.index') }}" id="tour-nav-inventory-transfers"
                                    {{ request()->routeIs('inventory.transfers.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inventory.transfers.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="arrow-left-right" class="w-3.5 h-3.5 {{ request()->routeIs('inventory.transfers.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Transfer Stok Gudang</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('inventory.view'))
                                <a href="{{ route('inventory.movements') }}" id="tour-nav-inventory-movements"
                                    {{ request()->routeIs('inventory.movements') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inventory.movements') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="history" class="w-3.5 h-3.5 {{ request()->routeIs('inventory.movements') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Kartu Mutasi Stok</span>
                                </a>
                            @endif
                        @endif

                        {{-- Subgroup: Data Master --}}
                        @if ($canAccessMasterData || \App\Support\Context::hasPermission('products.view'))
                            <div class="px-2 pt-2 pb-0.5 text-[11px] font-bold tracking-wider uppercase text-black/55 dark:text-white/55 select-none">
                                Data Master
                            </div>

                            @if (\App\Support\Context::hasPermission('master_data.product_categories.view'))
                                <a href="{{ route('product-categories.index') }}"
                                    {{ request()->routeIs('product-categories.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('product-categories.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="tags" class="w-3.5 h-3.5 {{ request()->routeIs('product-categories.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Kategori Produk</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('master_data.material_categories.view'))
                                <a href="{{ route('material-categories.index') }}"
                                    {{ request()->routeIs('material-categories.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('material-categories.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="tag" class="w-3.5 h-3.5 {{ request()->routeIs('material-categories.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Kategori Bahan Baku</span>
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

                            @if (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('materials.view'))
                                <a href="{{ route('import.index') }}" id="tour-nav-import"
                                    {{ request()->routeIs('import.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('import.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 {{ request()->routeIs('import.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Impor / Ekspor Data</span>
                                </a>
                            @endif
                        @endif
                    </div>

                    {{-- Submenu Melayang (Collapsed Flyout) --}}
                    <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Produk &amp; Persediaan
                        </div>
                        @if (\App\Support\Context::hasPermission('products.view'))
                            <a href="{{ route('products.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="package" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Katalog Produk &amp; Menu</span>
                            </a>
                            <a href="{{ route('services.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="wrench" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Jasa &amp; Layanan</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('materials.view'))
                            <a href="{{ route('materials.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="layers" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                <span>Bahan Baku &amp; Resep</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('inventory.view'))
                            <a href="{{ route('inventory.stocks') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="boxes" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Stok Gudang</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('warehouse.view'))
                            <a href="{{ route('warehouse.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="warehouse" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Lokasi Gudang</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('inventory.manage'))
                            <a href="{{ route('inventory.opnames.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Opname Stok Fisik</span>
                            </a>
                            <a href="{{ route('inventory.transfers.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="arrow-left-right" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Transfer Stok Gudang</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('inventory.view'))
                            <a href="{{ route('inventory.movements') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="history" class="w-3.5 h-3.5 text-[#8E8E93]"></i>
                                <span>Kartu Mutasi Stok</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 4: PEMBELIAN & SUPPLIER                             --}}
        {{-- ======================================================== --}}
        @if ($canAccessPurchasing)
            <div class="space-y-0.5 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-semibold tracking-wide uppercase text-black/55 dark:text-white/55 select-none">
                    Pembelian &amp; Supplier
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group" x-data="{ flyoutOpen: false }"
                    @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                    <button type="button" id="tour-group-purchasing" data-tour-group="purchasing"
                        @click="purchasingOpen = !purchasingOpen" role="button"
                        :aria-expanded="purchasingOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Pembelian & Supplier' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isPurchasingRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="truck"
                                class="w-4 h-4 {{ $isPurchasingRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
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
                        @if (\App\Support\Context::hasPermission('purchasing.view') || \App\Support\Context::hasPermission('purchasing.manage'))
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

                        @if (\App\Support\Context::hasPermission('master_data.suppliers.view') || \App\Support\Context::hasPermission('purchasing.view'))
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
                    <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Pembelian &amp; Supplier
                        </div>
                        @if (\App\Support\Context::hasPermission('purchasing.view') || \App\Support\Context::hasPermission('purchasing.manage'))
                            <a href="{{ route('purchase-orders.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Pesanan Pembelian (PO)</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('purchasing.bills'))
                            <a href="{{ route('purchasing.bills.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Tagihan Supplier</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('master_data.suppliers.view') || \App\Support\Context::hasPermission('purchasing.view'))
                            <a href="{{ route('suppliers.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Supplier &amp; Pemasok</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('purchase.returns'))
                            <a href="{{ route('purchase.returns.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="undo" class="w-3.5 h-3.5 text-[#FF2D55]"></i>
                                <span>Retur Pembelian</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 5: KEUANGAN, BIAYA & LAPORAN                        --}}
        {{-- ======================================================== --}}
        @if ($canAccessFinance || $canAccessCosting || $canAccessReports || $canAccessHrm)
            <div class="space-y-0.5 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-semibold tracking-wide uppercase text-black/55 dark:text-white/55 select-none">
                    Keuangan &amp; Laporan
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group" x-data="{ flyoutOpen: false }"
                    @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                    <button type="button" id="tour-group-finance" data-tour-group="finance"
                        @click="financeOpen = !financeOpen" role="button"
                        :aria-expanded="financeOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Keuangan, Biaya & Laporan' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isFinanceRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="wallet"
                                class="w-4 h-4 {{ $isFinanceRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Keuangan &amp; Laporan</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="financeOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    {{-- Submenu Terbuka (Expanded) --}}
                    <div x-show="financeOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        {{-- Subgroup: Kas & Akuntansi --}}
                        @if ($canAccessFinance)
                            <div class="px-2 pt-1 pb-0.5 text-[11px] font-bold tracking-wider uppercase text-black/55 dark:text-white/55 select-none">
                                Kas &amp; Akuntansi
                            </div>

                            @if (\App\Support\Context::hasPermission('finance.cash_bank'))
                                <a href="{{ route('finance.cash-bank.index') }}" id="tour-nav-cash-bank"
                                    {{ request()->routeIs('finance.cash-bank.index') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.cash-bank.index') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="landmark" class="w-3.5 h-3.5 {{ request()->routeIs('finance.cash-bank.index') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Kas &amp; Rekening Bank</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('expenses.view'))
                                <a href="{{ route('finance.expenses.index') }}" id="tour-nav-expenses"
                                    {{ request()->routeIs('finance.expenses.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.expenses.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="arrow-down-circle" class="w-3.5 h-3.5 {{ request()->routeIs('finance.expenses.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Pengeluaran Operasional</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('accounting.view'))
                                <a href="{{ route('finance.journals.index') }}"
                                    {{ request()->routeIs('finance.journals.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.journals.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="book-open" class="w-3.5 h-3.5 {{ request()->routeIs('finance.journals.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Buku Jurnal Keuangan</span>
                                </a>

                                <a href="{{ route('finance.cash-bank.ledger') }}"
                                    {{ request()->routeIs('finance.cash-bank.ledger') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.cash-bank.ledger') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 {{ request()->routeIs('finance.cash-bank.ledger') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Buku Besar Akun</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('finance.receivables'))
                                <a href="{{ route('finance.receivables') }}"
                                    {{ request()->routeIs('finance.receivables') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.receivables') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 {{ request()->routeIs('finance.receivables') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Daftar Piutang</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('finance.payables'))
                                <a href="{{ route('finance.payables') }}"
                                    {{ request()->routeIs('finance.payables') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.payables') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="arrow-down-left" class="w-3.5 h-3.5 {{ request()->routeIs('finance.payables') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Daftar Utang</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::isOwner())
                                <a href="{{ route('finance.settlements.index') }}"
                                    {{ request()->routeIs('finance.settlements.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.settlements.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="credit-card" class="w-3.5 h-3.5 {{ request()->routeIs('finance.settlements.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Pencairan Dana Penjualan</span>
                                </a>
                            @endif
                        @endif

                        {{-- Subgroup: Analisis HPP & Margin --}}
                        @if ($canAccessCosting)
                            <div class="px-2 pt-2 pb-0.5 text-[11px] font-bold tracking-wider uppercase text-black/55 dark:text-white/55 select-none">
                                Analisis HPP &amp; Biaya
                            </div>

                            @if (\App\Support\Context::hasPermission('costing.view_margin') || \App\Support\Context::hasPermission('costing.manage'))
                                <a href="{{ route('calculator.index') }}" id="tour-nav-calculator"
                                    {{ request()->routeIs('calculator.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('calculator.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="percent" class="w-3.5 h-3.5 {{ request()->routeIs('calculator.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Hitung HPP &amp; Margin</span>
                                </a>

                                <a href="{{ route('simulator.index') }}" id="tour-nav-simulator"
                                    {{ request()->routeIs('simulator.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('simulator.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="sliders" class="w-3.5 h-3.5 {{ request()->routeIs('simulator.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Simulator Harga Jual</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('labor_machines.view'))
                                <a href="{{ route('labor-machines.index') }}" id="tour-nav-labor-machines"
                                    {{ request()->routeIs('labor-machines.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('labor-machines.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="cog" class="w-3.5 h-3.5 {{ request()->routeIs('labor-machines.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Biaya Tenaga Kerja &amp; Mesin</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('costing.view_margin') || \App\Support\Context::hasPermission('reports.costing'))
                                <a href="{{ route('profitability.index') }}" id="tour-nav-profitability"
                                    {{ request()->routeIs('profitability.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('profitability.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="trending-up" class="w-3.5 h-3.5 {{ request()->routeIs('profitability.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Analisis Keuntungan Produk</span>
                                </a>
                            @endif
                        @endif

                        {{-- Subgroup: Laporan Bisnis --}}
                        @if ($canAccessReports)
                            <div class="px-2 pt-2 pb-0.5 text-[11px] font-bold tracking-wider uppercase text-black/55 dark:text-white/55 select-none">
                                Laporan Bisnis
                            </div>

                            @if (\App\Support\Context::hasPermission('reports.view'))
                                <a href="{{ route('reports.index') }}" id="tour-nav-reports"
                                    {{ request()->routeIs('reports.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('reports.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="bar-chart-3" class="w-3.5 h-3.5 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Semua Laporan Bisnis</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('pos.reports'))
                                <a href="{{ route('pos.reports.index') }}"
                                    {{ request()->routeIs('pos.reports.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.reports.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="pie-chart" class="w-3.5 h-3.5 {{ request()->routeIs('pos.reports.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Laporan Penjualan Kasir</span>
                                </a>
                            @endif
                        @endif

                        {{-- Subgroup: Staf & Gaji --}}
                        @if ($canAccessHrm)
                            <div class="px-2 pt-2 pb-0.5 text-[11px] font-bold tracking-wider uppercase text-black/55 dark:text-white/55 select-none">
                                Staf &amp; Gaji
                            </div>

                            <a href="{{ route('hrm.index') }}"
                                {{ request()->routeIs('hrm.index') || request()->routeIs('hrm.employees.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('hrm.index') || request()->routeIs('hrm.employees.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="user-check" class="w-3.5 h-3.5 {{ request()->routeIs('hrm.index') || request()->routeIs('hrm.employees.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Data Karyawan</span>
                            </a>

                            <a href="{{ route('hrm.payrolls.index') }}"
                                {{ request()->routeIs('hrm.payrolls.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('hrm.payrolls.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="banknote" class="w-3.5 h-3.5 {{ request()->routeIs('hrm.payrolls.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Gaji &amp; Slip Gaji</span>
                            </a>

                            <a href="{{ route('tax.index') }}"
                                {{ request()->routeIs('tax.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('tax.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="receipt-text" class="w-3.5 h-3.5 {{ request()->routeIs('tax.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Perhitungan Pajak Karyawan</span>
                            </a>
                        @endif
                    </div>

                    {{-- Submenu Melayang (Collapsed Flyout) --}}
                    <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Keuangan &amp; Laporan
                        </div>
                        @if (\App\Support\Context::hasPermission('finance.cash_bank'))
                            <a href="{{ route('finance.cash-bank.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="landmark" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Kas &amp; Rekening Bank</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('expenses.view'))
                            <a href="{{ route('finance.expenses.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="arrow-down-circle" class="w-3.5 h-3.5 text-[#FF3B30]"></i>
                                <span>Pengeluaran Operasional</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('accounting.view'))
                            <a href="{{ route('finance.journals.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="book-open" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Buku Jurnal Keuangan</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('costing.view_margin') || \App\Support\Context::hasPermission('costing.manage'))
                            <a href="{{ route('calculator.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="percent" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Hitung HPP &amp; Margin</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('reports.view'))
                            <a href="{{ route('reports.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="bar-chart-3" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Semua Laporan Bisnis</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 6: SALURAN & PEMASARAN                              --}}
        {{-- ======================================================== --}}
        @if ($canAccessStorefront || $canAccessChannels)
            <div class="space-y-0.5 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-semibold tracking-wide uppercase text-black/55 dark:text-white/55 select-none">
                    Saluran &amp; Pemasaran
                </div>
                <div x-show="sidebarCollapsed"
                    class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
                </div>

                <div class="relative group" x-data="{ flyoutOpen: false }"
                    @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                    <button type="button" id="tour-group-channels" data-tour-group="channels"
                        @click="channelsOpen = !channelsOpen" role="button"
                        :aria-expanded="channelsOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? 'Saluran & Pemasaran' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isChannelsRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="globe"
                                class="w-4 h-4 {{ $isChannelsRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Saluran &amp; Pemasaran</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="channelsOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    {{-- Submenu Terbuka (Expanded) --}}
                    <div x-show="channelsOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        {{-- Subgroup: Toko Online --}}
                        @if ($canAccessStorefront)
                            <div class="px-2 pt-1 pb-0.5 text-[11px] font-bold tracking-wider uppercase text-black/55 dark:text-white/55 select-none">
                                Toko Online
                            </div>

                            @if (\App\Support\Context::hasPermission('cms.manage') || \App\Support\Context::isOwner())
                                <a href="{{ route('landing-page.edit') }}"
                                    {{ request()->routeIs('landing-page.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('landing-page.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="layout-template" class="w-3.5 h-3.5 {{ request()->routeIs('landing-page.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Desain Halaman Toko</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('storefront.orders.view') || \App\Support\Context::isOwner())
                                <a href="{{ route('storefront.orders.index') }}"
                                    {{ request()->routeIs('storefront.orders.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('storefront.orders.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5 {{ request()->routeIs('storefront.orders.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Pesanan Toko Online</span>
                                </a>
                            @endif

                            @if ($sidebarShowReservation && (\App\Support\Context::hasPermission('storefront.reservations.manage') || \App\Support\Context::isOwner()))
                                <a href="{{ route('storefront.reservations.index') }}"
                                    {{ request()->routeIs('storefront.reservations.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('storefront.reservations.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="calendar-check" class="w-3.5 h-3.5 {{ request()->routeIs('storefront.reservations.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Reservasi &amp; Booking</span>
                                </a>
                            @endif

                            @if ($sidebarShowShipping && (\App\Support\Context::hasPermission('storefront.shipping.manage') || \App\Support\Context::isOwner()))
                                <a href="{{ route('storefront.shipping.index') }}"
                                    {{ request()->routeIs('storefront.shipping.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('storefront.shipping.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="truck" class="w-3.5 h-3.5 {{ request()->routeIs('storefront.shipping.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Ongkir &amp; Pengiriman</span>
                                </a>
                            @endif

                            @if (\App\Support\Context::hasPermission('storefront.manage') || \App\Support\Context::isOwner())
                                <a href="{{ route('storefront.settings.index') }}"
                                    {{ request()->routeIs('storefront.settings.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('storefront.settings.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="sliders" class="w-3.5 h-3.5 {{ request()->routeIs('storefront.settings.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Pengaturan Etalase</span>
                                </a>
                            @endif
                        @endif

                        {{-- Subgroup: WhatsApp & Pemasaran --}}
                        @if (\App\Support\Context::hasPermission('whatsapp.view') || \App\Support\Context::isOwner())
                            <div class="px-2 pt-2 pb-0.5 text-[11px] font-bold tracking-wider uppercase text-black/55 dark:text-white/55 select-none">
                                WhatsApp &amp; Pemasaran
                            </div>

                            <a href="{{ route('whatsapp.index') }}"
                                {{ request()->routeIs('whatsapp.index') || request()->routeIs('whatsapp.settings') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('whatsapp.index') || request()->routeIs('whatsapp.settings') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="message-square" class="w-3.5 h-3.5 {{ request()->routeIs('whatsapp.index') || request()->routeIs('whatsapp.settings') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">WhatsApp Bisnis</span>
                            </a>

                            @if (\App\Support\Context::hasPermission('whatsapp.manage') || \App\Support\Context::isOwner())
                                <a href="{{ route('whatsapp.broadcast.index') }}"
                                    {{ request()->routeIs('whatsapp.broadcast.*') ? 'aria-current="page"' : '' }}
                                    class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('whatsapp.broadcast.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                    <i data-lucide="send" class="w-3.5 h-3.5 {{ request()->routeIs('whatsapp.broadcast.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                    <span class="truncate">Pesan Siaran WhatsApp</span>
                                </a>
                            @endif

                            <a href="{{ route('whatsapp.logs.index') }}"
                                {{ request()->routeIs('whatsapp.logs.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('whatsapp.logs.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="list" class="w-3.5 h-3.5 {{ request()->routeIs('whatsapp.logs.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Riwayat Pesan Terkirim</span>
                            </a>
                        @endif

                        {{-- Subgroup: Media Sosial --}}
                        @if (\App\Support\Context::isOwner())
                            <div class="px-2 pt-2 pb-0.5 text-[11px] font-bold tracking-wider uppercase text-black/55 dark:text-white/55 select-none">
                                Media Sosial
                            </div>

                            <a href="{{ route('social-media.index') }}"
                                {{ request()->routeIs('social-media.index') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.index') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="share-2" class="w-3.5 h-3.5 {{ request()->routeIs('social-media.index') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Akun Media Sosial</span>
                            </a>

                            <a href="{{ route('social-media.posts.index') }}"
                                {{ request()->routeIs('social-media.posts.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.posts.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 {{ request()->routeIs('social-media.posts.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Jadwal Postingan</span>
                            </a>

                            <a href="{{ route('social-media.calendar') }}"
                                {{ request()->routeIs('social-media.calendar') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.calendar') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="calendar-days" class="w-3.5 h-3.5 {{ request()->routeIs('social-media.calendar') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Kalender Konten</span>
                            </a>

                            <a href="{{ route('social-media.insights.index') }}"
                                {{ request()->routeIs('social-media.insights.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.insights.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="line-chart" class="w-3.5 h-3.5 {{ request()->routeIs('social-media.insights.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">Analitik Media Sosial</span>
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
                    <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            Saluran &amp; Pemasaran
                        </div>
                        @if (\App\Support\Context::hasPermission('cms.manage') || \App\Support\Context::isOwner())
                            <a href="{{ route('landing-page.edit') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="layout-template" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Desain Halaman Toko</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('storefront.orders.view') || \App\Support\Context::isOwner())
                            <a href="{{ route('storefront.orders.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Pesanan Toko Online</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('whatsapp.view') || \App\Support\Context::isOwner())
                            <a href="{{ route('whatsapp.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="message-square" class="w-3.5 h-3.5 text-[#30D158]"></i>
                                <span>WhatsApp Bisnis</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- GRUP 7: PENGATURAN USAHA                                 --}}
        {{-- ======================================================== --}}
        @if ($canAccessSettings || $canAccessRoles || $canAccessBilling || \App\Support\Context::isOwner())
            <div class="space-y-0.5 pt-1">
                <div x-show="!sidebarCollapsed"
                    class="px-2.5 pt-1 pb-1 text-[11px] font-semibold tracking-wide uppercase text-black/55 dark:text-white/55 select-none">
                    Pengaturan Usaha
                </div>
            <div x-show="sidebarCollapsed"
                class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
            </div>

            <div class="relative group" x-data="{ flyoutOpen: false }"
                @mouseenter="if(sidebarCollapsed) flyoutOpen = true" @mouseleave="flyoutOpen = false">
                <button type="button" id="tour-group-settings" data-tour-group="settings"
                    @click="settingsOpen = !settingsOpen" role="button"
                    :aria-expanded="settingsOpen ? 'true' : 'false'"
                    :title="sidebarCollapsed ? 'Pengaturan Usaha' : ''"
                    class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isSettingsRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                    <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                        <i data-lucide="settings"
                            class="w-4 h-4 {{ $isSettingsRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
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
                        {{ request()->routeIs('profile.*') ? 'aria-current="page"' : '' }}
                        class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('profile.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <i data-lucide="user" class="w-3.5 h-3.5 {{ request()->routeIs('profile.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                        <span class="truncate">Profil Pengguna</span>
                    </a>

                    @if (\App\Support\Context::hasPermission('settings.view') || \App\Support\Context::isOwner())
                        <a href="{{ route('settings.index') }}" id="tour-nav-settings"
                            {{ request()->routeIs('settings.*') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('settings.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="sliders" class="w-3.5 h-3.5 {{ request()->routeIs('settings.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">Pengaturan Usaha &amp; Cabang</span>
                        </a>
                    @endif

                    @if (\App\Support\Context::hasPermission('roles.view') || \App\Support\Context::isOwner())
                        <a href="{{ route('roles.index') }}"
                            {{ request()->routeIs('roles.*') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('roles.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 {{ request()->routeIs('roles.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">Hak Akses &amp; Peran Staf</span>
                        </a>
                    @endif

                    @if (\App\Support\Context::hasPermission('billing.view') || \App\Support\Context::isOwner())
                        <a href="{{ route('billing') }}" id="tour-nav-billing"
                            {{ request()->routeIs('billing') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('billing') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="credit-card" class="w-3.5 h-3.5 {{ request()->routeIs('billing') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">Paket Berlangganan &amp; Kuota</span>
                        </a>

                        <a href="{{ route('billing.limits') }}"
                            {{ request()->routeIs('billing.limits') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('billing.limits') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="gauge" class="w-3.5 h-3.5 {{ request()->routeIs('billing.limits') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">Batas &amp; Pemakaian Kuota</span>
                        </a>
                    @endif

                    <a href="{{ route('feedback.bugs.index') }}"
                        {{ request()->routeIs('feedback.*') ? 'aria-current="page"' : '' }}
                        class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('feedback.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <i data-lucide="life-buoy" class="w-3.5 h-3.5 {{ request()->routeIs('feedback.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                        <span class="truncate">Bantuan &amp; Kontak Dukungan</span>
                    </a>
                </div>

                {{-- Submenu Melayang (Collapsed Flyout) --}}
                <div x-show="sidebarCollapsed && flyoutOpen" x-transition.opacity
                    class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                    style="display: none;">
                    <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                        Pengaturan Usaha
                    </div>
                    <a href="{{ route('profile.edit') }}"
                        class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                        <i data-lucide="user" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Profil Pengguna</span>
                    </a>
                    @if (\App\Support\Context::hasPermission('settings.view') || \App\Support\Context::isOwner())
                        <a href="{{ route('settings.index') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="sliders" class="w-3.5 h-3.5 text-[#8E8E93]"></i>
                            <span>Pengaturan Usaha &amp; Cabang</span>
                        </a>
                    @endif
                    @if (\App\Support\Context::hasPermission('billing.view') || \App\Support\Context::isOwner())
                        <a href="{{ route('billing') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="credit-card" class="w-3.5 h-3.5 text-[#34C759]"></i>
                            <span>Paket Berlangganan</span>
                        </a>
                    @endif
                    <a href="{{ route('feedback.bugs.index') }}"
                        class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                        <i data-lucide="life-buoy" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                        <span>Bantuan &amp; Dukungan</span>
                    </a>
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
                                <span class="text-[12px] font-semibold text-black dark:text-white truncate">Cooca UMKM</span>
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
                                Tingkatkan ke Cooca UMKM ›
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</aside>
