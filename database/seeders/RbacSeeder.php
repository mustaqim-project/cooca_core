<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

final class RbacSeeder extends Seeder
{
    /**
     * All module permissions — single source of truth.
     * Format: 'module.action' => ['name', 'category', 'description']
     */
    private function permissionMatrix(): array
    {
        return [
            // ─── Dashboard ───────────────────────────────────────────
            'dashboard.view'              => ['Lihat Dashboard',                        'dashboard',   'Akses halaman ringkasan & statistik bisnis.'],

            // ─── Customers / CRM ─────────────────────────────────────
            'customers.view'              => ['Lihat Daftar Pelanggan',                 'customers',   'Melihat daftar dan detail pelanggan.'],
            'customers.create'            => ['Tambah Pelanggan',                       'customers',   'Menambahkan pelanggan baru.'],
            'customers.edit'              => ['Edit Pelanggan',                         'customers',   'Mengubah data pelanggan.'],
            'customers.delete'            => ['Hapus Pelanggan',                        'customers',   'Menghapus data pelanggan.'],
            'customers.export'            => ['Export Pelanggan',                       'customers',   'Export data pelanggan ke Excel/CSV.'],

            // ─── Products & BOM ──────────────────────────────────────
            'products.view'               => ['Lihat Katalog Produk',                   'products',    'Melihat daftar produk dan resep BOM.'],
            'products.create'             => ['Tambah Produk',                          'products',    'Menambahkan produk baru ke katalog.'],
            'products.edit'               => ['Edit Produk',                            'products',    'Mengubah data produk dan resep BOM.'],
            'products.delete'             => ['Hapus Produk',                           'products',    'Menghapus produk dari katalog.'],
            'products.manage'             => ['Kelola Produk & BOM Lengkap',            'products',    'Akses lengkap manajemen produk, BOM, dan resep.'],

            // ─── Materials / Bahan Baku ───────────────────────────────
            'materials.view'              => ['Lihat Bahan Baku',                       'materials',   'Melihat daftar bahan baku dan harga.'],
            'materials.create'            => ['Tambah Bahan Baku',                      'materials',   'Menambahkan bahan baku baru.'],
            'materials.edit'              => ['Edit Bahan Baku',                        'materials',   'Mengubah data dan harga bahan baku.'],
            'materials.delete'            => ['Hapus Bahan Baku',                       'materials',   'Menghapus data bahan baku.'],

            // ─── Costing / HPP ───────────────────────────────────────
            'costing.view_margin'         => ['Lihat HPP & Margin',                     'costing',     'Melihat harga modal dasar (HPP) dan persentase margin laba.'],
            'costing.manage'              => ['Kalkulasi & Edit Resep HPP',              'costing',     'Membuat dan mengedit formula resep BOM serta menghitung HPP.'],

            // ─── Invoices / Faktur ────────────────────────────────────
            'invoices.view'               => ['Lihat Faktur',                           'invoices',    'Melihat daftar dan detail faktur penjualan.'],
            'invoices.create'             => ['Buat Faktur',                            'invoices',    'Menerbitkan faktur penjualan baru.'],
            'invoices.edit'               => ['Edit Faktur',                            'invoices',    'Mengubah faktur yang belum dikirim.'],
            'invoices.delete'             => ['Hapus Faktur',                           'invoices',    'Menghapus faktur penjualan.'],
            'invoices.record_payment'     => ['Catat Pembayaran Faktur',                'invoices',    'Mencatat pelunasan piutang faktur.'],
            'invoices.export'             => ['Export Faktur',                          'invoices',    'Export data faktur ke Excel/CSV.'],

            // ─── Sales Pipeline (Quotation & SO) ─────────────────────
            'sales.view'                  => ['Lihat Penawaran & Pesanan',              'sales',       'Melihat daftar quotation dan sales order.'],
            'sales.pipeline'              => ['Kelola Penawaran & Pesanan Penjualan',   'sales',       'Membuat dan mengelola surat penawaran harga dan pesanan penjualan.'],

            // ─── POS ─────────────────────────────────────────────────
            'pos.terminal'                => ['Operasikan Kasir POS',                   'pos',         'Mengoperasikan kasir, checkout pesanan, dan cetak struk.'],
            'pos.supervisor_pin'          => ['Otorisasi Void & Diskon Supervisor',     'pos',         'Otorisasi pembatalan pesanan kasir, refund, dan diskon manual.'],
            'pos.reports'                 => ['Laporan POS & Shift',                    'pos',         'Melihat laporan transaksi kasir, shift, dan summary penjualan.'],
            'pos.reports_export'          => ['Export Laporan POS',                     'pos',         'Export laporan POS ke Excel.'],

            // ─── Purchasing / PO ─────────────────────────────────────
            'purchasing.view'             => ['Lihat Purchase Order',                   'purchasing',  'Melihat daftar dan detail purchase order.'],
            'purchasing.manage'           => ['Kelola Purchase Order & Pemasok',        'purchasing',  'Membuat pesanan pembelian ke pemasok dan kelola database vendor.'],

            // ─── Receiving / Penerimaan Barang ───────────────────────
            'receiving.manage'            => ['Penerimaan Barang (Receiving)',           'receiving',   'Mencatat fisik barang masuk dari PO atau pembelian langsung ke stok.'],

            // ─── Inventory / Stok ─────────────────────────────────────
            'inventory.view'              => ['Lihat Stok',                             'inventory',   'Melihat stok real-time dan riwayat mutasi.'],
            'inventory.manage'            => ['Mutasi & Opname Stok',                   'inventory',   'Kelola transfer gudang, penyesuaian stok, dan rekonsiliasi opname.'],

            // ─── Expenses / Beban ─────────────────────────────────────
            'expenses.view'               => ['Lihat Beban Operasional',                'expenses',    'Melihat daftar pengeluaran operasional.'],
            'expenses.manage'             => ['Catat Beban / Biaya',                    'expenses',    'Mencatat pengeluaran operasional toko dan lampiran bukti nota.'],

            // ─── Accounting / Buku Besar ──────────────────────────────
            'accounting.view'             => ['Lihat Buku Besar & Jurnal',              'accounting',  'Melihat bagan akun (COA) dan entri jurnal pembukuan ganda.'],

            // ─── Reports ─────────────────────────────────────────────
            'reports.view'                => ['Lihat Laporan',                          'reports',     'Akses halaman laporan dan analitik.'],
            'reports.financial'           => ['Laporan Finansial (Laba/Rugi)',           'reports',     'Melihat laporan laba rugi, arus kas, dan analitik pendapatan.'],
            'reports.export'              => ['Export Laporan',                          'reports',     'Export laporan ke Excel/PDF.'],
            'reports.costing'             => ['Laporan HPP & Kalkulasi',                'reports',     'Akses laporan kalkulator HPP dan profitabilitas.'],

            // ─── Settings ─────────────────────────────────────────────
            'settings.view'               => ['Lihat Pengaturan Bisnis',                'settings',    'Melihat halaman pengaturan bisnis.'],
            'settings.edit'               => ['Edit Pengaturan Bisnis',                 'settings',    'Mengubah identitas, logo, cabang, dan konfigurasi umum bisnis.'],

            // ─── Master Data CMS ─────────────────────────────────────
            'master_data.suppliers.view'             => ['Lihat Supplier',           'master_data', 'Melihat daftar supplier bisnis.'],
            'master_data.suppliers.manage'           => ['Kelola Supplier',          'master_data', 'Menambah, mengubah, dan menghapus supplier bisnis.'],
            'master_data.material_categories.view'   => ['Lihat Kategori Bahan',     'master_data', 'Melihat kategori bahan baku bisnis.'],
            'master_data.material_categories.manage' => ['Kelola Kategori Bahan',    'master_data', 'Menambah, mengubah, dan menghapus kategori bahan baku.'],
            'master_data.product_categories.view'    => ['Lihat Kategori Produk',    'master_data', 'Melihat kategori produk bisnis.'],
            'master_data.product_categories.manage'  => ['Kelola Kategori Produk',   'master_data', 'Menambah, mengubah, dan menghapus kategori produk.'],
            'master_data.units.view'                 => ['Lihat Satuan Ukur',        'master_data', 'Melihat satuan sistem dan satuan bisnis.'],
            'master_data.units.manage'               => ['Kelola Satuan Ukur',       'master_data', 'Menambah, mengubah, menghapus, dan mengatur konversi satuan.'],

            // ─── User / Member Management ─────────────────────────────
            'users.view'                  => ['Lihat Anggota Tim',                      'members',     'Melihat daftar anggota tim bisnis.'],
            'users.manage'                => ['Kelola Anggota & Undang Karyawan',       'members',     'Mengundang, edit role, dan hapus karyawan dari workspace.'],
            'users.create'                => ['Tambah User',                             'users',       'Menambahkan user atau employee ke bisnis.'],
            'users.edit'                  => ['Edit User',                               'users',       'Mengubah data user atau employee.'],
            'users.delete'                => ['Hapus User',                              'users',       'Menghapus user dari bisnis.'],
            'users.show'                  => ['Detail User',                             'users',       'Melihat detail user atau employee.'],

            // ─── Role & Permission Management ─────────────────────────
            'roles.view'                  => ['Lihat Role & Hak Akses',                 'roles',       'Melihat daftar role dan permission yang berlaku.'],
            'roles.manage'                => ['Kelola Role & Permission Karyawan',      'roles',       'Membuat, edit, dan hapus role serta assign permission.'],
            'roles.create'                => ['Tambah Role',                             'roles',       'Membuat role custom baru.'],
            'roles.edit'                  => ['Edit Role',                               'roles',       'Mengubah nama, deskripsi, dan permission role.'],
            'roles.delete'                => ['Hapus Role',                              'roles',       'Menghapus role custom yang tidak digunakan.'],
            'roles.show'                  => ['Detail Role',                             'roles',       'Melihat detail role dan permission.'],

            // ─── Client / Customer Alias ─────────────────────────────
            'clients.manage'              => ['Kelola Client',                           'client',      'Mengelola seluruh data client bisnis.'],
            'clients.create'              => ['Tambah Client',                           'client',      'Menambahkan client baru.'],
            'clients.edit'                => ['Edit Client',                             'client',      'Mengubah data client.'],
            'clients.delete'              => ['Hapus Client',                            'client',      'Menghapus data client.'],
            'clients.show'                => ['Detail Client',                           'client',      'Melihat detail client.'],

            // ─── Billing / SaaS ───────────────────────────────────────
            'billing.view'                => ['Lihat Paket & Tagihan',                  'billing',     'Melihat paket langganan dan riwayat tagihan.'],
            'billing.manage'              => ['Kelola Langganan & Upgrade',             'billing',     'Mengelola paket langganan Cooca UMKM, upgrade, dan riwayat tagihan.'],

            // ─── AI Assistant ─────────────────────────────────────────
            'ai.access'                   => ['AI Assistant & Rekomendasi',             'ai',          'Mengakses fitur prediksi stok, analitik pintar, dan chatbot AI.'],

            // ─── Labor & Machine ──────────────────────────────────────
            'labor_machines.view'         => ['Lihat Upah Kerja & Mesin',               'labor',       'Melihat daftar tarif upah kerja dan biaya mesin.'],
            'labor_machines.manage'       => ['Kelola Upah Kerja & Mesin',              'labor',       'Menambah dan mengubah tarif upah kerja dan biaya mesin produksi.'],
        ];
    }

    /**
     * Role definitions with their permission slugs.
     */
    private function roleDefinitions(array $permsBySlug): array
    {
        $allSlugs       = array_keys($this->permissionMatrix());
        // Business-level permissions (owner gets all EXCEPT system-level — owner IS the system for their business)
        $ownerSlugs     = $allSlugs;
        $managerSlugs   = [
            'dashboard.view',
            'customers.view', 'customers.create', 'customers.edit', 'customers.export',
            'products.view', 'products.create', 'products.edit', 'products.manage',
            'materials.view', 'materials.create', 'materials.edit',
            'costing.view_margin', 'costing.manage',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.record_payment', 'invoices.export',
            'sales.view', 'sales.pipeline',
            'pos.terminal', 'pos.supervisor_pin', 'pos.reports', 'pos.reports_export',
            'purchasing.view', 'purchasing.manage',
            'receiving.manage',
            'inventory.view', 'inventory.manage',
            'expenses.view', 'expenses.manage',
            'accounting.view',
            'reports.view', 'reports.financial', 'reports.export', 'reports.costing',
            'settings.view',
            'master_data.suppliers.view', 'master_data.suppliers.manage',
            'master_data.material_categories.view', 'master_data.material_categories.manage',
            'master_data.product_categories.view', 'master_data.product_categories.manage',
            'master_data.units.view', 'master_data.units.manage',
            'users.view',
            'labor_machines.view', 'labor_machines.manage',
            'ai.access',
        ];

        return [
            'owner' => [
                'name'        => 'Owner',
                'description' => 'Pemilik bisnis dengan akses mutlak ke seluruh modul operasional dan finansial bisnis.',
                'permissions' => $ownerSlugs,
            ],
            'manager' => [
                'name'        => 'Manager / Supervisor',
                'description' => 'Manajer operasional dengan akses penuh kecuali kelola role, billing, dan penghapusan data.',
                'permissions' => $managerSlugs,
            ],
            'admin' => [
                'name'        => 'Admin Operasional',
                'description' => 'Administrator operasional dengan akses hampir penuh kecuali billing dan platform settings.',
                'permissions' => array_merge($managerSlugs, ['users.manage', 'roles.view']),
            ],
            'cashier' => [
                'name'        => 'Kasir / Sales',
                'description' => 'Operasional kasir POS dan penjualan. Dilarang melihat HPP modal dan margin rahasia.',
                'permissions' => [
                    'dashboard.view',
                    'customers.view', 'customers.create',
                    'sales.view', 'sales.pipeline',
                    'invoices.view', 'invoices.create', 'invoices.record_payment',
                    'pos.terminal',
                    'inventory.view',
                    'ai.access',
                ],
            ],
            'warehouse' => [
                'name'        => 'Staf Gudang / Logistik',
                'description' => 'Penerimaan barang dari PO, mutasi inventori, dan stock opname fisik.',
                'permissions' => [
                    'dashboard.view',
                    'products.view',
                    'materials.view',
                    'inventory.view', 'inventory.manage',
                    'purchasing.view',
                    'receiving.manage',
                    'ai.access',
                ],
            ],
            'finance' => [
                'name'        => 'Staf Keuangan',
                'description' => 'Pengelolaan faktur, pembayaran, pencatatan beban, buku besar, dan laporan keuangan.',
                'permissions' => [
                    'dashboard.view',
                    'customers.view',
                    'costing.view_margin',
                    'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.record_payment', 'invoices.export',
                    'purchasing.view', 'purchasing.manage',
                    'expenses.view', 'expenses.manage',
                    'accounting.view',
                    'reports.view', 'reports.financial', 'reports.export',
                    'ai.access',
                ],
            ],
            'staff' => [
                'name'        => 'Staf Operasional',
                'description' => 'Staf serbaguna untuk kasir, pencatatan penjualan dan penerimaan inventori.',
                'permissions' => [
                    'dashboard.view',
                    'customers.view',
                    'products.view',
                    'sales.view', 'sales.pipeline',
                    'invoices.view', 'invoices.create', 'invoices.record_payment',
                    'pos.terminal',
                    'inventory.view',
                    'receiving.manage',
                    'ai.access',
                ],
            ],
            'viewer' => [
                'name'        => 'Viewer (Read-Only)',
                'description' => 'Akses hanya lihat untuk peninjauan laporan dan transaksi tanpa hak edit.',
                'permissions' => [
                    'dashboard.view',
                    'reports.view', 'reports.financial',
                    'accounting.view',
                    'invoices.view',
                    'inventory.view',
                    'customers.view',
                ],
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ── 1. Seed all permissions ───────────────────────────────────
        $permsBySlug = [];
        foreach ($this->permissionMatrix() as $slug => [$name, $category, $description]) {
            $permsBySlug[$slug] = Permission::updateOrCreate(
                ['slug' => $slug],
                compact('name', 'category', 'description')
            );
        }

        $this->command?->info('  ✓ ' . count($permsBySlug) . ' permissions seeded.');

        // ── 2. Seed global roles (business_id = null) ─────────────────
        foreach ($this->roleDefinitions($permsBySlug) as $slug => $data) {
            /** @var Role $role */
            $role = Role::updateOrCreate(
                ['business_id' => null, 'slug' => $slug],
                [
                    'name'        => $data['name'],
                    'description' => $data['description'],
                ]
            );

            $syncData = [];
            foreach ($data['permissions'] as $permSlug) {
                if (isset($permsBySlug[$permSlug])) {
                    $syncData[$permsBySlug[$permSlug]->id] = [
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                    ];
                }
            }

            $role->permissions()->sync($syncData);

            $this->command?->line("  ✓ Role [{$slug}] → " . count($syncData) . ' permissions synced.');
        }

        $this->command?->info('  ✓ RBAC seeder complete.');
    }
}
