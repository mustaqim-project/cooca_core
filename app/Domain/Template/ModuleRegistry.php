<?php

declare(strict_types=1);

namespace App\Domain\Template;

final class ModuleRegistry
{
    public const MODULE_POS_DINEIN = 'pos_dinein';
    public const MODULE_POS_RETAIL = 'pos_retail';
    public const MODULE_B2B_SALES = 'b2b_sales';
    public const MODULE_RECIPE_BOM = 'recipe_bom';
    public const MODULE_LABOR_MACHINES = 'labor_machines';
    public const MODULE_INVENTORY_WAREHOUSE = 'inventory_warehouse';
    public const MODULE_PROCUREMENT = 'procurement';
    public const MODULE_CRM_LOYALTY = 'crm_loyalty';
    public const MODULE_CHANNELS_MARKETING = 'channels_marketing';
    public const MODULE_STOREFRONT_CHECKOUT = 'storefront_checkout';
    public const MODULE_ORDER_REQUEST = 'order_request';
    public const MODULE_SCHEDULED_ORDER = 'scheduled_order';
    public const MODULE_CUSTOMER_PO = 'customer_po';
    public const MODULE_RESERVATION = 'reservation';
    public const MODULE_MERCHANT_SHIPPING = 'merchant_shipping';

    /**
     * Complete definition of all modular features in Cooca.
     *
     * @return array<string, array{
     *     name: string,
     *     description: string,
     *     icon: string,
     *     category: string,
     *     permissions: array<int, string>
     * }>
     */
    public static function definitions(): array
    {
        return [
            self::MODULE_POS_DINEIN => [
                'name' => 'Meja & Kitchen Display (KDS)',
                'description' => 'Tata letak meja makan, cetak kartu QR meja, dan layar antrean pesanan dapur restoran.',
                'icon' => 'layout-grid',
                'category' => 'Operasional Resto',
                'permissions' => [
                    'pos.tables',
                    'pos.kitchen',
                ],
            ],
            self::MODULE_POS_RETAIL => [
                'name' => 'Kasir POS & Struk Cepat',
                'description' => 'Operasional mesin kasir, buka/tutup shift kasir, riwayat struk, dan otorisasi supervisor.',
                'icon' => 'calculator',
                'category' => 'Penjualan',
                'permissions' => [
                    'pos.terminal',
                    'pos.orders',
                    'pos.supervisor_pin',
                    'pos.reports',
                    'pos.reports_export',
                ],
            ],
            self::MODULE_B2B_SALES => [
                'name' => 'Penjualan B2B, Penawaran & Invoice',
                'description' => 'Surat penawaran harga (quotation), pesanan penjualan (sales order), faktur tagihan B2B, dan retur.',
                'icon' => 'file-text',
                'category' => 'Penjualan B2B',
                'permissions' => [
                    'sales.view',
                    'sales.pipeline',
                    'invoices.view',
                    'invoices.create',
                    'invoices.edit',
                    'invoices.delete',
                    'invoices.record_payment',
                    'invoices.export',
                    'sales.returns',
                ],
            ],
            self::MODULE_RECIPE_BOM => [
                'name' => 'Resep Formula BOM & Bahan Baku',
                'description' => 'Katalog bahan baku mentah, formula resep Bill of Materials (BOM), dan penghitungan waste.',
                'icon' => 'boxes',
                'category' => 'Produksi & HPP',
                'permissions' => [
                    'materials.view',
                    'materials.create',
                    'materials.edit',
                    'materials.delete',
                    'costing.manage',
                    'master_data.material_categories.view',
                    'master_data.material_categories.manage',
                ],
            ],
            self::MODULE_LABOR_MACHINES => [
                'name' => 'Upah Kerja Langsung & Mesin Produksi',
                'description' => 'Pencatatan tarif upah per jam kerja (man-hours), komisi, dan biaya depresiasi jam mesin.',
                'icon' => 'users-2',
                'category' => 'Produksi & HPP',
                'permissions' => [
                    'labor_machines.view',
                    'labor_machines.manage',
                ],
            ],
            self::MODULE_INVENTORY_WAREHOUSE => [
                'name' => 'Multi-Gudang & Stock Opname Fisik',
                'description' => 'Manajemen multi-lokasi gudang, transfer mutasi antar cabang, dan rekonsiliasi opname berkala.',
                'icon' => 'warehouse',
                'category' => 'Inventori',
                'permissions' => [
                    'inventory.manage',
                    'warehouse.view',
                    'warehouse.manage',
                ],
            ],
            self::MODULE_PROCUREMENT => [
                'name' => 'Pengadaan PO & Hutang Supplier',
                'description' => 'Purchase Order (PO), pencatatan tagihan invoice vendor (bills), dan retur barang ke supplier.',
                'icon' => 'truck',
                'category' => 'Pembelian',
                'permissions' => [
                    'purchasing.view',
                    'purchasing.manage',
                    'purchasing.bills',
                    'purchase.returns',
                    'receiving.manage',
                    'master_data.suppliers.view',
                    'master_data.suppliers.manage',
                ],
            ],
            self::MODULE_CRM_LOYALTY => [
                'name' => 'CRM, Poin Loyalitas & Voucher',
                'description' => 'Tingkatan tier membership pelanggan, perolehan reward poin transaksi, dan kupon voucher diskon.',
                'icon' => 'award',
                'category' => 'Pemasaran',
                'permissions' => [
                    'crm.view',
                    'crm.manage',
                    'customers.export',
                ],
            ],
            self::MODULE_CHANNELS_MARKETING => [
                'name' => 'WhatsApp Gateway & Landing Page CMS',
                'description' => 'Pengiriman pesan struk WhatsApp, broadcast blast promosi, dan website profil mini publik.',
                'icon' => 'send',
                'category' => 'Saluran',
                'permissions' => [
                    'whatsapp.view',
                    'whatsapp.manage',
                    'cms.manage',
                ],
            ],
            self::MODULE_STOREFRONT_CHECKOUT => [
                'name' => 'Toko Online & Checkout Mandiri',
                'description' => 'Etalase storefront publik, keranjang belanja, checkout instan, dan verifikasi bukti transfer.',
                'icon' => 'shopping-bag',
                'category' => 'Toko Online',
                'permissions' => [
                    'storefront.manage',
                    'storefront.orders.view',
                    'storefront.orders.process',
                ],
            ],
            self::MODULE_ORDER_REQUEST => [
                'name' => 'Pengajuan Pesanan Kustom (Request Order)',
                'description' => 'Formulir pengajuan pesanan khusus, penawaran harga, dan negosiasi spesifikasi pelanggan.',
                'icon' => 'message-square',
                'category' => 'Toko Online',
                'permissions' => [
                    'storefront.orders.view',
                    'storefront.orders.process',
                ],
            ],
            self::MODULE_SCHEDULED_ORDER => [
                'name' => 'Pesanan Terjadwal & Slot Waktu',
                'description' => 'Kalender tanggal pemesanan, jam cut-off harian, lead-time persiapan, dan batas kapasitas kuota.',
                'icon' => 'calendar-clock',
                'category' => 'Toko Online',
                'permissions' => [
                    'storefront.orders.view',
                    'storefront.orders.process',
                ],
            ],
            self::MODULE_CUSTOMER_PO => [
                'name' => 'Purchase Order Klien & PO Batch',
                'description' => 'Penerimaan PO klien B2B dengan jadwal pengiriman multi-drop bertahap dan faktur formal.',
                'icon' => 'layers',
                'category' => 'Toko Online',
                'permissions' => [
                    'storefront.po.manage',
                    'storefront.orders.view',
                    'storefront.orders.process',
                ],
            ],
            self::MODULE_RESERVATION => [
                'name' => 'Reservasi Meja & Booking Jadwal',
                'description' => 'Pemesanan slot waktu layanan jasa, alokasi meja restoran, dan batas kuota tamu.',
                'icon' => 'calendar-check',
                'category' => 'Toko Online',
                'permissions' => [
                    'storefront.reservations.manage',
                ],
            ],
            self::MODULE_MERCHANT_SHIPPING => [
                'name' => 'Aturan Ongkir & Kurir Toko',
                'description' => 'Pengaturan biaya kirim mandiri (pickup, flat rate, radius jarak km, dan gratis ongkir).',
                'icon' => 'truck',
                'category' => 'Toko Online',
                'permissions' => [
                    'storefront.shipping.manage',
                ],
            ],
        ];
    }

    /**
     * Map each of the 25 industry templates to the modules that should be disabled by default.
     *
     * @return array<string, array<int, string>>
     */
    public static function templateDisabledModulesMap(): array
    {
        return [
            // ─── 1. F&B RESTORAN & DINE-IN ────────────────────────────────────
            'fnb_resto' => [
                self::MODULE_B2B_SALES,
                self::MODULE_LABOR_MACHINES,
            ],
            'fnb_cafe' => [
                self::MODULE_B2B_SALES,
                self::MODULE_LABOR_MACHINES,
                self::MODULE_INVENTORY_WAREHOUSE,
            ],

            // ─── 2. F&B NON DINE-IN ──────────────────────────────────────────
            'fnb_bakery' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_B2B_SALES,
            ],
            'fnb_cloud_kitchen' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_B2B_SALES,
                self::MODULE_LABOR_MACHINES,
            ],
            'fnb_catering' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
            ],
            'fnb_frozen_food' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
            ],
            'fnb_catering_diet' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
                self::MODULE_LABOR_MACHINES,
                self::MODULE_INVENTORY_WAREHOUSE,
            ],

            // ─── 3. MANUFAKTUR & PRODUKSI ────────────────────────────────────
            'mfg_garment' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
            ],
            'mfg_precision' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
            ],
            'mfg_furniture' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
            ],
            'mfg_craft' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_INVENTORY_WAREHOUSE,
            ],
            'mfg_printing' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
            ],
            'mfg_cosmetics' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
            ],
            'mfg_tailor_custom' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
                self::MODULE_INVENTORY_WAREHOUSE,
            ],

            // ─── 4. RETAIL & TOKO OBAT ───────────────────────────────────────
            'retail_reseller' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_B2B_SALES,
                self::MODULE_RECIPE_BOM,
                self::MODULE_LABOR_MACHINES,
            ],
            'retail_pharmacy' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_B2B_SALES,
                self::MODULE_RECIPE_BOM,
                self::MODULE_LABOR_MACHINES,
            ],

            // ─── 5. JASA PROFESIONAL & PROYEK B2B ───────────────────────────
            'service_agency' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
                self::MODULE_RECIPE_BOM,
                self::MODULE_INVENTORY_WAREHOUSE,
                self::MODULE_PROCUREMENT,
            ],
            'service_contractor' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
                self::MODULE_RECIPE_BOM,
            ],
            'service_event' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
                self::MODULE_RECIPE_BOM,
                self::MODULE_INVENTORY_WAREHOUSE,
            ],

            // ─── 6. JASA OPERASIONAL ON-THE-SPOT ─────────────────────────────
            'service_workshop' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_RECIPE_BOM,
            ],
            'service_barbershop' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_B2B_SALES,
                self::MODULE_RECIPE_BOM,
                self::MODULE_INVENTORY_WAREHOUSE,
                self::MODULE_PROCUREMENT,
            ],
            'service_laundry' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_B2B_SALES,
                self::MODULE_RECIPE_BOM,
                self::MODULE_INVENTORY_WAREHOUSE,
                self::MODULE_PROCUREMENT,
            ],
            'service_autodetailing' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_B2B_SALES,
                self::MODULE_RECIPE_BOM,
                self::MODULE_INVENTORY_WAREHOUSE,
                self::MODULE_PROCUREMENT,
            ],

            // ─── 7. DISTRIBUSI & PERTANIAN ───────────────────────────────────
            'distributor_fmcg' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
                self::MODULE_RECIPE_BOM,
                self::MODULE_LABOR_MACHINES,
            ],
            'agri_farming' => [
                self::MODULE_POS_DINEIN,
                self::MODULE_POS_RETAIL,
                self::MODULE_LABOR_MACHINES,
            ],
        ];
    }

    /**
     * Get default disabled modules for a given template code.
     *
     * @return array<int, string>
     */
    public static function getDisabledModulesForTemplate(?string $templateCode): array
    {
        if (empty($templateCode)) {
            return [];
        }

        return self::templateDisabledModulesMap()[$templateCode] ?? [];
    }

    /**
     * Check which module a specific permission belongs to.
     */
    public static function getModuleForPermission(string $permissionSlug): ?string
    {
        foreach (self::definitions() as $moduleKey => $meta) {
            if (in_array($permissionSlug, $meta['permissions'], true)) {
                return $moduleKey;
            }
        }

        return null;
    }

    /**
     * Get list of permission slugs that belong to the given module.
     *
     * @return array<int, string>
     */
    public static function getPermissionsForModule(string $moduleKey): array
    {
        return self::definitions()[$moduleKey]['permissions'] ?? [];
    }

    /**
     * Summary of enabled and disabled features for UI presentation during registration.
     *
     * @return array{
     *     enabled: array<int, string>,
     *     disabled: array<int, string>
     * }
     */
    public static function getFeaturesSummaryForTemplate(?string $templateCode): array
    {
        $disabled = self::getDisabledModulesForTemplate($templateCode);
        $enabled = [];
        $disabledNames = [];

        foreach (self::definitions() as $key => $def) {
            if (in_array($key, $disabled, true)) {
                $disabledNames[] = $def['name'];
            } else {
                $enabled[] = $def['name'];
            }
        }

        return [
            'enabled' => $enabled,
            'disabled' => $disabledNames,
        ];
    }
}
