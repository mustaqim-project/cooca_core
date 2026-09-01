<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'Kelola Pengaturan Bisnis', 'slug' => 'business.settings', 'category' => 'business', 'description' => 'Kelola identitas, logo, cabang, dan konfigurasi umum bisnis.'],
            ['name' => 'Kelola Anggota & Role', 'slug' => 'users.manage', 'category' => 'members', 'description' => 'Undang, edit, dan hapus karyawan serta atur hak akses peran.'],
            ['name' => 'Master Produk & Resep BOM', 'slug' => 'products.manage', 'category' => 'product', 'description' => 'Kelola katalog produk, kategori, satuan, dan bahan baku.'],
            ['name' => 'Lihat HPP Riil & Margin Toko', 'slug' => 'costing.view_margin', 'category' => 'costing', 'description' => 'Melihat harga modal dasar (HPP) dan persentase margin laba.'],
            ['name' => 'Kalkulasi & Edit Resep HPP', 'slug' => 'costing.manage', 'category' => 'costing', 'description' => 'Membuat dan mengedit formula resep BOM serta menghitung HPP.'],
            ['name' => 'Quotation & Sales Order', 'slug' => 'sales.pipeline', 'category' => 'sales', 'description' => 'Membuat dan mengelola surat penawaran harga dan pesanan penjualan.'],
            ['name' => 'Faktur Penjualan (Invoice)', 'slug' => 'invoices.manage', 'category' => 'sales', 'description' => 'Menerbitkan faktur penjualan resmi dan mencatat pelunasan piutang.'],
            ['name' => 'POS Kasir Terminal', 'slug' => 'pos.terminal', 'category' => 'pos', 'description' => 'Mengoperasikan kasir, checkout pesanan, dan cetak struk.'],
            ['name' => 'Otorisasi Void & Diskon Supervisor', 'slug' => 'pos.supervisor_pin', 'category' => 'pos', 'description' => 'Otorisasi pembatalan pesanan kasir, refund, dan diskon manual khusus.'],
            ['name' => 'Purchase Order & Pemasok', 'slug' => 'purchasing.manage', 'category' => 'purchasing', 'description' => 'Membuat pesanan pembelian ke pemasok dan kelola database vendor.'],
            ['name' => 'Penerimaan Barang (Receiving)', 'slug' => 'receiving.manage', 'category' => 'purchasing', 'description' => 'Mencatat fisik barang masuk dari PO atau pembelian langsung ke stok.'],
            ['name' => 'Mutasi & Opname Stok', 'slug' => 'inventory.manage', 'category' => 'inventory', 'description' => 'Kelola transfer gudang, penyesuaian stok, dan rekonsiliasi opname.'],
            ['name' => 'Pencatatan Beban / Biaya', 'slug' => 'expenses.manage', 'category' => 'finance', 'description' => 'Mencatat pengeluaran operasional toko dan lampiran bukti nota.'],
            ['name' => 'Buku Besar & Jurnal Otomatis', 'slug' => 'accounting.view', 'category' => 'finance', 'description' => 'Melihat bagan akun (COA) dan entri jurnal pembukuan ganda.'],
            ['name' => 'Laporan Finansial (Laba/Rugi)', 'slug' => 'reports.financial', 'category' => 'reports', 'description' => 'Melihat laporan laba rugi, arus kas, dan analitik pendapatan.'],
            ['name' => 'AI Assistant & Rekomendasi', 'slug' => 'ai.access', 'category' => 'ai', 'description' => 'Mengakses fitur prediksi stok, analitik pintar, dan chatbot AI.'],
            ['name' => 'SaaS Billing & Langganan', 'slug' => 'billing.manage', 'category' => 'billing', 'description' => 'Mengelola paket langganan Cooca Core, upgrade, dan riwayat tagihan.'],
        ];

        $createdPermissions = [];
        foreach ($permissions as $perm) {
            $createdPermissions[$perm['slug']] = Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                [
                    'name' => $perm['name'],
                    'category' => $perm['category'],
                    'description' => $perm['description'],
                ]
            );
        }

        $roles = [
            'owner' => [
                'name' => 'Owner',
                'description' => 'Pemilik bisnis dengan akses mutlak ke seluruh modul operasional dan finansial.',
                'permissions' => array_keys($createdPermissions),
            ],
            'admin' => [
                'name' => 'Admin Operasional',
                'description' => 'Administrator operasional dengan akses penuh ke seluruh modul kecuali SaaS Billing.',
                'permissions' => [
                    'business.settings',
                    'users.manage',
                    'products.manage',
                    'costing.view_margin',
                    'costing.manage',
                    'sales.pipeline',
                    'invoices.manage',
                    'pos.terminal',
                    'pos.supervisor_pin',
                    'purchasing.manage',
                    'receiving.manage',
                    'inventory.manage',
                    'expenses.manage',
                    'accounting.view',
                    'reports.financial',
                    'ai.access',
                ],
            ],
            'cashier' => [
                'name' => 'Kasir / Sales',
                'description' => 'Operasional kasir POS dan penjualan. Dilarang melihat HPP modal dan margin rahasia.',
                'permissions' => [
                    'pos.terminal',
                    'sales.pipeline',
                    'invoices.manage',
                    'ai.access',
                ],
            ],
            'warehouse' => [
                'name' => 'Staf Gudang / Logistik',
                'description' => 'Penerimaan barang dari PO, mutasi inventori, dan stock opname fisik.',
                'permissions' => [
                    'receiving.manage',
                    'inventory.manage',
                    'ai.access',
                ],
            ],
            'finance' => [
                'name' => 'Staf Keuangan',
                'description' => 'Pengelolaan faktur, pembayaran, pencatatan beban, buku besar, dan laporan keuangan.',
                'permissions' => [
                    'costing.view_margin',
                    'invoices.manage',
                    'purchasing.manage',
                    'expenses.manage',
                    'accounting.view',
                    'reports.financial',
                    'ai.access',
                ],
            ],
            'staff' => [
                'name' => 'Staf Operasional',
                'description' => 'Staf serbaguna untuk kasir, pencatatan penjualan dan penerimaan inventori.',
                'permissions' => [
                    'pos.terminal',
                    'sales.pipeline',
                    'receiving.manage',
                    'inventory.manage',
                    'ai.access',
                ],
            ],
            'viewer' => [
                'name' => 'Viewer (Read-Only)',
                'description' => 'Akses hanya lihat untuk peninjauan laporan dan transaksi tanpa hak edit.',
                'permissions' => [
                    'accounting.view',
                    'reports.financial',
                ],
            ],
        ];

        foreach ($roles as $slug => $data) {
            /** @var Role $role */
            $role = Role::updateOrCreate(
                ['business_id' => null, 'slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                ]
            );

            $permissionIds = collect($data['permissions'])
                ->filter(fn (string $slug) => isset($createdPermissions[$slug]))
                ->map(fn (string $slug) => $createdPermissions[$slug]->id)
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}

