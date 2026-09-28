<?php

declare(strict_types=1);

namespace App\Support\Navigation;

use App\Domain\Template\ModuleRegistry;
use App\Support\Context;

final class NavigationRegistry
{
    /**
     * Complete definition of all modules and their persistent secondary tab collections.
     *
     * @return array<string, array{
     *     key: string,
     *     label: string,
     *     icon: string,
     *     parent_breadcrumb: array{label: string, route: string},
     *     module: ?string,
     *     tabs: array<int, array{
     *         key: string,
     *         label: string,
     *         route: string,
     *         params?: array<string, mixed>,
     *         active_routes: array<int, string>,
     *         permission?: ?string,
     *         module?: ?string,
     *         badge?: ?string
     *     }>
     * }>
     */
    public static function all(): array
    {
        return [
            'finance' => [
                'key'               => 'finance',
                'label'             => 'Keuangan',
                'icon'              => 'wallet',
                'parent_breadcrumb' => ['label' => 'Keuangan & Kas', 'route' => 'finance.cash-bank.index'],
                'module'            => null,
                'tabs'              => [
                    [
                        'key'           => 'cash-bank',
                        'label'         => 'Kas & Rekening',
                        'icon'          => 'wallet-cards',
                        'route'         => 'finance.cash-bank.index',
                        'active_routes' => ['finance.cash-bank.index'],
                        'permission'    => 'finance.cash_bank',
                    ],
                    [
                        'key'           => 'ledger',
                        'label'         => 'Buku Kas & Mutasi',
                        'icon'          => 'book-open',
                        'route'         => 'finance.cash-bank.ledger',
                        'active_routes' => ['finance.cash-bank.ledger*'],
                        'permission'    => 'finance.cash_bank',
                    ],
                    [
                        'key'           => 'expenses',
                        'label'         => 'Beban Operasional',
                        'icon'          => 'receipt',
                        'route'         => 'finance.expenses.index',
                        'active_routes' => ['finance.expenses.*', 'expenses.index'],
                        'permission'    => 'expenses.view',
                    ],
                    [
                        'key'           => 'journals',
                        'label'         => 'Jurnal Akuntansi',
                        'icon'          => 'file-spreadsheet',
                        'route'         => 'finance.journals.index',
                        'active_routes' => ['finance.journals.*'],
                        'permission'    => 'accounting.view',
                    ],
                    [
                        'key'           => 'receivables',
                        'label'         => 'Piutang (AR)',
                        'icon'          => 'clock',
                        'route'         => 'finance.receivables',
                        'active_routes' => ['finance.receivables*'],
                        'permission'    => 'finance.receivables',
                    ],
                    [
                        'key'           => 'payables',
                        'label'         => 'Hutang (AP)',
                        'icon'          => 'arrow-up-right',
                        'route'         => 'finance.payables',
                        'active_routes' => ['finance.payables*'],
                        'permission'    => 'finance.payables',
                    ],
                    [
                        'key'           => 'settlements',
                        'label'         => 'Payout Hub',
                        'icon'          => 'credit-card',
                        'route'         => 'finance.settlements.index',
                        'active_routes' => ['finance.settlements.*'],
                        'permission'    => 'finance.cash_bank',
                    ],
                    [
                        'key'           => 'payout-accounts',
                        'label'         => 'Rekening Payout',
                        'icon'          => 'landmark',
                        'route'         => 'finance.payout-accounts.index',
                        'active_routes' => ['finance.payout-accounts.*'],
                        'permission'    => 'finance.cash_bank',
                    ],
                    [
                        'key'           => 'edc-terminals',
                        'label'         => 'Mesin EDC',
                        'icon'          => 'terminal',
                        'route'         => 'finance.edc-terminals.index',
                        'active_routes' => ['finance.edc-terminals.*'],
                        'permission'    => 'finance.cash_bank',
                    ],
                    [
                        'key'           => 'external-recon',
                        'label'         => 'Rekonsiliasi & Likuiditas',
                        'icon'          => 'layout-dashboard',
                        'route'         => 'finance.external-recon.index',
                        'active_routes' => ['finance.external-recon.*', 'finance.liquidity-dashboard', 'finance.discrepancies'],
                        'permission'    => 'finance.cash_bank',
                    ],
                ],

            ],

            'accounting' => [
                'key'               => 'accounting',
                'label'             => 'Akuntansi & Pembukuan',
                'icon'              => 'book-open',
                'parent_breadcrumb' => ['label' => 'Keuangan & Akuntansi', 'route' => 'finance.coa.index'],
                'module'            => ModuleRegistry::MODULE_ACCOUNTING_CORPORATE,
                'tabs'              => [
                    [
                        'key'           => 'coa',
                        'label'         => 'Bagan Akun (COA)',
                        'icon'          => 'list-tree',
                        'route'         => 'finance.coa.index',
                        'active_routes' => ['finance.coa.*'],
                        'permission'    => 'accounting.view',
                    ],
                    [
                        'key'           => 'ledger',
                        'label'         => 'Buku Besar Umum',
                        'icon'          => 'book-open',
                        'route'         => 'finance.general-ledger',
                        'active_routes' => ['finance.general-ledger*'],
                        'permission'    => 'accounting.view',
                    ],
                    [
                        'key'           => 'trial-balance',
                        'label'         => 'Neraca Saldo',
                        'icon'          => 'scale',
                        'route'         => 'finance.trial-balance',
                        'active_routes' => ['finance.trial-balance*'],
                        'permission'    => 'accounting.view',
                    ],
                    [
                        'key'           => 'balance-sheet',
                        'label'         => 'Neraca Keuangan SAK',
                        'icon'          => 'file-spreadsheet',
                        'route'         => 'finance.balance-sheet',
                        'active_routes' => ['finance.balance-sheet*'],
                        'permission'    => 'accounting.view',
                    ],
                    [
                        'key'           => 'reconciliations',
                        'label'         => 'Rekonsiliasi Bank',
                        'icon'          => 'refresh-cw',
                        'route'         => 'finance.reconciliations.index',
                        'active_routes' => ['finance.reconciliations.*'],
                        'permission'    => 'accounting.view',
                    ],
                ],
            ],

            'purchasing' => [
                'key'               => 'purchasing',
                'label'             => 'Pembelian & Pemasok',
                'icon'              => 'truck',
                'parent_breadcrumb' => ['label' => 'Pembelian & Pemasok', 'route' => 'purchase-orders.index'],
                'module'            => ModuleRegistry::MODULE_PROCUREMENT,
                'tabs'              => [
                    [
                        'key'           => 'po',
                        'label'         => 'Pesanan Pembelian (PO)',
                        'icon'          => 'shopping-bag',
                        'route'         => 'purchase-orders.index',
                        'active_routes' => ['purchase-orders.*', 'purchasing.receipts.*'],
                        'permission'    => 'purchasing.view',
                    ],
                    [
                        'key'           => 'bills',
                        'label'         => 'Tagihan Vendor (Bills)',
                        'icon'          => 'receipt',
                        'route'         => 'purchasing.bills.index',
                        'active_routes' => ['purchasing.bills.*'],
                        'permission'    => 'purchasing.bills',
                    ],
                    [
                        'key'           => 'returns',
                        'label'         => 'Retur Pembelian',
                        'icon'          => 'undo-2',
                        'route'         => 'purchase.returns.index',
                        'active_routes' => ['purchase.returns.*'],
                        'permission'    => 'purchase.returns',
                    ],
                    [
                        'key'           => 'suppliers',
                        'label'         => 'Pemasok & Vendor',
                        'icon'          => 'truck',
                        'route'         => 'suppliers.index',
                        'active_routes' => ['suppliers.*'],
                        'permission'    => 'master_data.suppliers.view',
                    ],
                ],
            ],

            'products' => [
                'key'               => 'products',
                'label'             => 'Katalog Produk',
                'icon'              => 'package',
                'parent_breadcrumb' => ['label' => 'Produk', 'route' => 'products.index'],
                'module'            => null,
                'tabs'              => [
                    [
                        'key'           => 'catalog',
                        'label'         => 'Katalog Produk',
                        'icon'          => 'package',
                        'route'         => 'products.index',
                        'active_routes' => ['products.index', 'products.show', 'products.create', 'products.edit', 'products.bom', 'products.branch_prices.*'],
                        'permission'    => 'products.view',
                    ],
                    [
                        'key'           => 'services',
                        'label'         => 'Jasa & Layanan',
                        'icon'          => 'briefcase',
                        'route'         => 'services.index',
                        'active_routes' => ['services.*'],
                        'permission'    => 'products.view',
                    ],
                    [
                        'key'           => 'modifiers',
                        'label'         => 'Varian & Modifiers',
                        'icon'          => 'sliders',
                        'route'         => 'pos.modifiers.index',
                        'active_routes' => ['pos.modifiers.*'],
                        'permission'    => 'pos.modifiers',
                    ],
                    [
                        'key'           => 'marketplace',
                        'label'         => 'Marketplace & Multi-Harga',
                        'icon'          => 'store',
                        'route'         => 'marketplace-hub.products',
                        'active_routes' => ['marketplace-hub.products*'],
                        'permission'    => 'products.view',
                    ],
                    [
                        'key'           => 'categories',
                        'label'         => 'Kategori Produk',
                        'icon'          => 'folder-tree',
                        'route'         => 'product-categories.index',
                        'active_routes' => ['product-categories.*'],
                        'permission'    => 'master_data.product_categories.view',
                    ],
                    [
                        'key'           => 'units',
                        'label'         => 'Satuan Ukur (Units)',
                        'icon'          => 'ruler',
                        'route'         => 'units.index',
                        'params'        => ['from' => 'products'],
                        'active_routes' => ['units.*'],
                        'permission'    => 'master_data.units.view',
                    ],
                ],
            ],

            'materials' => [
                'key'               => 'materials',
                'label'             => 'Bahan Baku & Resep',
                'icon'              => 'boxes',
                'parent_breadcrumb' => ['label' => 'Bahan Baku', 'route' => 'materials.index'],
                'module'            => ModuleRegistry::MODULE_RECIPE_BOM,
                'tabs'              => [
                    [
                        'key'           => 'catalog',
                        'label'         => 'Katalog Bahan Baku',
                        'icon'          => 'boxes',
                        'route'         => 'materials.index',
                        'active_routes' => ['materials.index', 'materials.show', 'materials.create', 'materials.edit'],
                        'permission'    => 'materials.view',
                    ],
                    [
                        'key'           => 'categories',
                        'label'         => 'Kategori Bahan',
                        'icon'          => 'folder-tree',
                        'route'         => 'material-categories.index',
                        'active_routes' => ['material-categories.*'],
                        'permission'    => 'master_data.material_categories.view',
                    ],
                    [
                        'key'           => 'units',
                        'label'         => 'Satuan Ukur (Units)',
                        'icon'          => 'ruler',
                        'route'         => 'units.index',
                        'params'        => ['from' => 'materials'],
                        'active_routes' => ['units.*'],
                        'permission'    => 'master_data.units.view',
                    ],
                ],
            ],

            'inventory' => [
                'key'               => 'inventory',
                'label'             => 'Inventori & Logistik',
                'icon'              => 'warehouse',
                'parent_breadcrumb' => ['label' => 'Inventori & Logistik', 'route' => 'inventory.stocks'],
                'module'            => null,
                'tabs'              => [
                    [
                        'key'           => 'stocks',
                        'label'         => 'Ringkasan Stok',
                        'icon'          => 'boxes',
                        'route'         => 'inventory.stocks',
                        'active_routes' => ['inventory.stocks*'],
                        'permission'    => 'inventory.view',
                    ],
                    [
                        'key'           => 'warehouses',
                        'label'         => 'Lokasi Gudang',
                        'icon'          => 'warehouse',
                        'route'         => 'warehouse.index',
                        'active_routes' => ['warehouse.*'],
                        'permission'    => 'warehouse.view',
                        'module'        => ModuleRegistry::MODULE_INVENTORY_WAREHOUSE,
                    ],
                    [
                        'key'           => 'transfers',
                        'label'         => 'Transfer Stok',
                        'icon'          => 'arrow-left-right',
                        'route'         => 'inventory.transfers.index',
                        'active_routes' => ['inventory.transfers.*'],
                        'permission'    => 'inventory.manage',
                    ],
                    [
                        'key'           => 'opnames',
                        'label'         => 'Stok Opname',
                        'icon'          => 'clipboard-check',
                        'route'         => 'inventory.opnames.index',
                        'active_routes' => ['inventory.opnames.*'],
                        'permission'    => 'inventory.manage',
                    ],
                    [
                        'key'           => 'movements',
                        'label'         => 'Riwayat Mutasi',
                        'icon'          => 'history',
                        'route'         => 'inventory.movements',
                        'active_routes' => ['inventory.movements*'],
                        'permission'    => 'inventory.view',
                    ],
                ],
            ],

            'sales' => [
                'key'               => 'sales',
                'label'             => 'Penjualan B2B',
                'icon'              => 'file-text',
                'parent_breadcrumb' => ['label' => 'Penjualan B2B', 'route' => 'sales.orders.index'],
                'module'            => ModuleRegistry::MODULE_B2B_SALES,
                'tabs'              => [
                    [
                        'key'           => 'orders',
                        'label'         => 'Pesanan Penjualan (SO)',
                        'icon'          => 'shopping-cart',
                        'route'         => 'sales.orders.index',
                        'active_routes' => ['sales.orders.*'],
                        'permission'    => 'sales.view',
                    ],
                    [
                        'key'           => 'quotations',
                        'label'         => 'Surat Penawaran',
                        'icon'          => 'file-text',
                        'route'         => 'sales.quotations.index',
                        'active_routes' => ['sales.quotations.*'],
                        'permission'    => 'sales.view',
                    ],
                    [
                        'key'           => 'invoices',
                        'label'         => 'Faktur Penjualan',
                        'icon'          => 'receipt',
                        'route'         => 'invoices.index',
                        'active_routes' => ['invoices.*'],
                        'permission'    => 'invoices.view',
                    ],
                    [
                        'key'           => 'returns',
                        'label'         => 'Retur Penjualan',
                        'icon'          => 'undo-2',
                        'route'         => 'sales.returns.index',
                        'active_routes' => ['sales.returns.*'],
                        'permission'    => 'sales.returns',
                    ],
                ],
            ],

            'crm' => [
                'key'               => 'crm',
                'label'             => 'Pelanggan & CRM',
                'icon'              => 'users',
                'parent_breadcrumb' => ['label' => 'Pelanggan & CRM', 'route' => 'customers.index'],
                'module'            => null,
                'tabs'              => [
                    [
                        'key'           => 'customers',
                        'label'         => 'Buku Pelanggan',
                        'icon'          => 'users',
                        'route'         => 'customers.index',
                        'active_routes' => ['customers.*'],
                        'permission'    => 'customers.view',
                    ],
                    [
                        'key'           => 'members',
                        'label'         => 'Member & Tingkatan',
                        'icon'          => 'award',
                        'route'         => 'crm.members.index',
                        'active_routes' => ['crm.members.*'],
                        'permission'    => 'crm.view',
                        'module'        => ModuleRegistry::MODULE_CRM_LOYALTY,
                    ],
                    [
                        'key'           => 'vouchers',
                        'label'         => 'Voucher Promo',
                        'icon'          => 'ticket',
                        'route'         => 'crm.vouchers.index',
                        'active_routes' => ['crm.vouchers.*'],
                        'permission'    => 'crm.view',
                        'module'        => ModuleRegistry::MODULE_CRM_LOYALTY,
                    ],
                ],
            ],

            'communication' => [
                'key'               => 'communication',
                'label'             => 'Pusat Komunikasi',
                'icon'              => 'message-circle',
                'parent_breadcrumb' => ['label' => 'Komunikasi', 'route' => 'whatsapp.index'],
                'module'            => ModuleRegistry::MODULE_CHANNELS_MARKETING,
                'tabs'              => [
                    [
                        'key'           => 'whatsapp',
                        'label'         => 'WhatsApp Inbox',
                        'icon'          => 'message-square',
                        'route'         => 'whatsapp.index',
                        'active_routes' => ['whatsapp.index*', 'whatsapp.logs.*'],
                        'permission'    => 'whatsapp.view',
                    ],
                    [
                        'key'           => 'broadcast',
                        'label'         => 'WhatsApp Broadcast',
                        'icon'          => 'radio',
                        'route'         => 'whatsapp.broadcast.index',
                        'active_routes' => ['whatsapp.broadcast*'],
                        'permission'    => 'whatsapp.manage',
                    ],
                    [
                        'key'           => 'social',
                        'label'         => 'Media Sosial',
                        'icon'          => 'share-2',
                        'route'         => 'social-media.index',
                        'active_routes' => ['social-media.*'],
                        'permission'    => 'whatsapp.view',
                    ],
                ],
            ],

            'storefront' => [
                'key'               => 'storefront',
                'label'             => 'Toko Online',
                'icon'              => 'shopping-bag',
                'parent_breadcrumb' => ['label' => 'Toko Online', 'route' => 'storefront.orders.index'],
                'module'            => ModuleRegistry::MODULE_STOREFRONT_CHECKOUT,
                'tabs'              => [
                    [
                        'key'           => 'orders',
                        'label'         => 'Pesanan Online',
                        'icon'          => 'shopping-bag',
                        'route'         => 'storefront.orders.index',
                        'active_routes' => ['storefront.orders.*'],
                        'permission'    => 'storefront.orders.view',
                    ],
                    [
                        'key'           => 'shipping',
                        'label'         => 'Pengiriman & Ekspedisi',
                        'icon'          => 'truck',
                        'route'         => 'storefront.shipping.index',
                        'active_routes' => ['storefront.shipping.*'],
                        'permission'    => 'storefront.shipping.manage',
                        'module'        => ModuleRegistry::MODULE_MERCHANT_SHIPPING,
                    ],
                    [
                        'key'           => 'reservations',
                        'label'         => 'Reservasi & Booking',
                        'icon'          => 'calendar',
                        'route'         => 'storefront.reservations.index',
                        'active_routes' => ['storefront.reservations.*'],
                        'permission'    => 'storefront.reservations.manage',
                        'module'        => ModuleRegistry::MODULE_RESERVATION,
                    ],
                    [
                        'key'           => 'settings',
                        'label'         => 'Pengaturan Etalase',
                        'icon'          => 'settings',
                        'route'         => 'storefront.settings.index',
                        'active_routes' => ['storefront.settings.*'],
                        'permission'    => 'storefront.manage',
                    ],
                    [
                        'key'           => 'landing_page',
                        'label'         => 'Desain Toko (CMS)',
                        'icon'          => 'layout',
                        'route'         => 'landing-page.edit',
                        'active_routes' => ['landing-page.*'],
                        'permission'    => 'cms.manage',
                        'module'        => ModuleRegistry::MODULE_CHANNELS_MARKETING,
                    ],
                ],
            ],

            'approvals' => [
                'key'               => 'approvals',
                'label'             => 'Pusat Persetujuan',
                'icon'              => 'shield-check',
                'parent_breadcrumb' => ['label' => 'Persetujuan', 'route' => 'approvals.inbox'],
                'module'            => null,
                'tabs'              => [
                    [
                        'key'           => 'inbox',
                        'label'         => 'Kotak Masuk',
                        'route'         => 'approvals.inbox',
                        'active_routes' => ['approvals.inbox*'],
                        'permission'    => 'approvals.view',
                    ],
                    [
                        'key'           => 'history',
                        'label'         => 'Riwayat Dokumen',
                        'route'         => 'approvals.history',
                        'active_routes' => ['approvals.history*'],
                        'permission'    => 'approvals.view',
                    ],
                    [
                        'key'           => 'rules',
                        'label'         => 'Aturan Plafon Approval',
                        'route'         => 'approval-rules.index',
                        'active_routes' => ['approval-rules.*'],
                        'permission'    => 'approvals.manage',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get module definition by key.
     */
    public static function getModule(string $moduleKey): ?array
    {
        return self::all()[$moduleKey] ?? null;
    }

    /**
     * Get active tabs for the specified module, filtering out unauthorized or disabled tabs.
     *
     * @return array<int, array{key: string, label: string, url: string, is_active: bool, badge?: ?string}>
     */
    public static function getTabsForModule(string $moduleKey): array
    {
        $module = self::getModule($moduleKey);
        if ($module === null) {
            return [];
        }

        // If parent module requirement is disabled for business, return empty
        if (! empty($module['module'])) {
            $business = Context::business();
            if ($business && $business->isModuleDisabled($module['module'])) {
                return [];
            }
        }

        $tabs = [];
        foreach ($module['tabs'] as $tab) {
            if (! self::canAccessTab($tab)) {
                continue;
            }

            $routeUrl = route($tab['route'], $tab['params'] ?? []);
            $isActive = self::isTabActive($tab);

            $tabs[] = [
                'key'       => $tab['key'],
                'label'     => $tab['label'],
                'url'       => $routeUrl,
                'is_active' => $isActive,
                'icon'      => $tab['icon'] ?? null,
                'badge'     => $tab['badge'] ?? null,
            ];
        }

        return $tabs;
    }

    /**
     * Check whether current user & business can access the given tab.
     */
    public static function canAccessTab(array $tabConfig): bool
    {
        $business = Context::business();

        // 1. Module requirement check
        if (! empty($tabConfig['module']) && $business) {
            if ($business->isModuleDisabled($tabConfig['module'])) {
                return false;
            }
        }

        // 2. Permission requirement check
        if (! empty($tabConfig['permission'])) {
            if (! Context::hasPermission($tabConfig['permission'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if a tab is currently active.
     */
    public static function isTabActive(array $tabConfig): bool
    {
        $activeRoutes = $tabConfig['active_routes'] ?? [];

        foreach ($activeRoutes as $pattern) {
            if (request()->routeIs($pattern)) {
                // If specific params are required, check query params
                if (! empty($tabConfig['params'])) {
                    $matches = true;
                    foreach ($tabConfig['params'] as $k => $v) {
                        $queryVal = request()->query($k);
                        // Default fallback: if from is not passed, treat as materials
                        if ($k === 'from' && empty($queryVal) && $v === 'materials') {
                            continue;
                        }
                        if ($queryVal !== (string) $v) {
                            $matches = false;
                            break;
                        }
                    }
                    if ($matches) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Automatically resolve module and active tab for current route.
     *
     * @return array{
     *     module_key: ?string,
     *     module: ?array,
     *     active_tab_key: ?string,
     *     tabs: array,
     *     breadcrumbs: array<int, array{label: string, url: ?string}>
     * }
     */
    public static function getContextForCurrentRoute(): array
    {
        $all = self::all();

        foreach ($all as $modKey => $modDef) {
            foreach ($modDef['tabs'] as $tab) {
                if (self::isTabActive($tab)) {
                    $tabs = self::getTabsForModule($modKey);
                    $breadcrumbs = [
                        ['label' => 'Dashboard', 'url' => route('dashboard')],
                        ['label' => $modDef['parent_breadcrumb']['label'], 'url' => route($modDef['parent_breadcrumb']['route'])],
                        ['label' => $tab['label'], 'url' => null],
                    ];

                    return [
                        'module_key'     => $modKey,
                        'module'         => $modDef,
                        'active_tab_key' => $tab['key'],
                        'tabs'           => $tabs,
                        'breadcrumbs'    => $breadcrumbs,
                    ];
                }
            }
        }

        return [
            'module_key'     => null,
            'module'         => null,
            'active_tab_key' => null,
            'tabs'           => [],
            'breadcrumbs'    => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
            ],
        ];
    }

    /**
     * Get active module key for current route.
     */
    public static function getActiveModuleKey(): ?string
    {
        return self::getContextForCurrentRoute()['module_key'] ?? null;
    }
}

