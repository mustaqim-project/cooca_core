# Cetak Biru Database RBAC, Matriks Permission & Sinkronisasi Seeder

Dokumen ini memuat arsitektur database Role-Based Access Control (RBAC), format standar registrasi permission di `RbacSeeder.php`, pemetaan default roles, dan protokol sinkronisasi ke UI serta sidebar navigasi di ekosistem COOCA ERP & POS.

---

## 🗄️ 1. Skema Database RBAC COOCA

Struktur tabel otorisasi multi-tenant COOCA dirancang tangguh dan mendukung role bawaan sistem (*system default*) maupun custom role per bisnis:

```
┌─────────────────┐       ┌────────────────────────┐       ┌─────────────────┐
│   PERMISSIONS   │       │    PERMISSION_ROLE     │       │      ROLES      │
├─────────────────┤       ├────────────────────────┤       ├─────────────────┤
│ id              │◀──────│ permission_id          │──────▶│ id              │
│ name            │       │ role_id                │       │ business_id     │ (null = system role)
│ slug            │       └────────────────────────┘       │ name            │
│ category        │                                        │ slug            │
│ description     │                                        │ is_system       │
│ created_at      │                                        │ created_at      │
└─────────────────┘                                        └─────────────────┘
                                                                    ▲
                                                                    │
                                                       ┌────────────────────────┐
                                                       │  BUSINESS_MEMBERSHIPS  │
                                                       ├────────────────────────┤
                                                       │ id                     │
                                                       │ business_id            │
                                                       │ user_id                │
                                                       │ role                   │ (slug/enum)
                                                       │ custom_role_id         │ (fk roles.id)
                                                       └────────────────────────┘
```

---

## 📋 2. Format Standar Registrasi Permission (`RbacSeeder.php`)

Seluruh permission didefinisikan secara terpusat (*Single Source of Truth*) di dalam method `permissionMatrix()` pada `database/seeders/RbacSeeder.php`:

```php
// Format: 'modul.submodul.aksi' => ['Label Human-Readable', 'kategori', 'Deskripsi Fungsional']

private function permissionMatrix(): array
{
    return [
        // ─── Master Data (Kategori Produk, Satuan & Konversi) ───────────
        'master_data.product_categories.view'   => ['Lihat Kategori Produk',    'master_data', 'Melihat daftar kategori produk bisnis.'],
        'master_data.product_categories.manage' => ['Kelola Kategori Produk',   'master_data', 'Menambah, mengubah, dan menghapus kategori produk.'],
        'master_data.units.view'                => ['Lihat Satuan Barang',      'master_data', 'Melihat master satuan unit barang dan rasio.'],
        'master_data.units.manage'              => ['Kelola Satuan & Konversi', 'master_data', 'Menambah, mengubah satuan unit dan rumus konversi barang/bahan.'],

        // ─── Customers / Pelanggan CRM ────────────────────────────────
        'customers.view'                        => ['Lihat Daftar Pelanggan',   'customers',   'Melihat daftar dan detail profil pelanggan.'],
        'customers.create'                      => ['Tambah Pelanggan Baru',    'customers',   'Mendaftarkan pelanggan baru ke database CRM.'],
        'customers.edit'                        => ['Edit Data Pelanggan',      'customers',   'Mengubah informasi kontak dan plafon piutang pelanggan.'],
        'customers.delete'                      => ['Hapus Pelanggan',          'customers',   'Menghapus data pelanggan dari sistem.'],
        'customers.export'                      => ['Export Data Pelanggan',    'customers',   'Mengekspor database pelanggan ke format Excel/CSV.'],

        // ─── Pembelian / Purchasing & PO ──────────────────────────────
        'purchasing.view'                       => ['Lihat Purchase Order',     'purchasing',  'Melihat daftar dan rincian PO pembelian.'],
        'purchasing.manage'                     => ['Kelola PO & Pemasok',      'purchasing',  'Membuat, mengubah, dan membatalkan pesanan pembelian ke vendor.'],
        'purchasing.bills'                      => ['Tagihan & Hutang Vendor',  'purchasing',  'Mencatat dan mengelola faktur tagihan pembelian dari vendor.'],
        'receiving.manage'                      => ['Penerimaan Barang Fisik',  'purchasing',  'Mencatat barang masuk di gudang dari penerimaan PO.'],

        // ─── Kasir & POS Offline ──────────────────────────────────────
        'pos.terminal'                          => ['Operasikan Kasir POS',     'pos',         'Membuka sesi shift kasir, input pesanan, dan cetak struk.'],
        'pos.supervisor_pin'                    => ['Otorisasi Supervisor Kasir','pos',        'Otorisasi pembatalan transaksi, void nota, dan diskon manual.'],
        'pos.reports'                           => ['Laporan Penjualan Kasir',  'pos',         'Melihat laporan harian kasir dan rekonsiliasi kas shift.'],

        // ─── Inventori & Gudang ───────────────────────────────────────
        'inventory.view'                        => ['Lihat Stok Persediaan',    'inventory',   'Melihat saldo stok real-time per gudang dan kartu stok.'],
        'inventory.manage'                      => ['Mutasi & Opname Stok',     'inventory',   'Melakukan penyesuaian stok opname dan transfer antar-gudang.'],
        'warehouse.manage'                      => ['Kelola Master Gudang',     'inventory',   'Menambah, mengedit, dan mengatur lokasi rak gudang.'],

        // ─── Keuangan & Akuntansi ─────────────────────────────────────
        'accounting.view'                       => ['Lihat Jurnal & Buku Besar','accounting',  'Melihat bagan akun COA dan mutasi jurnal akuntansi.'],
        'accounting.manage'                     => ['Kelola Akun & Jurnal',     'accounting',  'Membuat jurnal manual dan mengatur pemetaan akun COA.'],
        'finance.cash_bank'                     => ['Buku Kas & Bank',          'accounting',  'Mencatat mutasi kas masuk/keluar dan rekonsiliasi bank.'],
    ];
}
```

---

## 👥 3. Matriks Pemetaan Default Role ke Permission

Seeder memetakan kumpulan permission ke dalam role bawaan (*System Roles*):

```php
private function rolePermissionMatrix(): array
{
    return [
        'admin' => [
            // Admin bisnis memiliki seluruh permission operasional & master data
            'master_data.product_categories.view',
            'master_data.product_categories.manage',
            'master_data.units.view',
            'master_data.units.manage',
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            'customers.export',
            'purchasing.view',
            'purchasing.manage',
            'purchasing.bills',
            'receiving.manage',
            'inventory.view',
            'inventory.manage',
            'warehouse.manage',
            'accounting.view',
            'accounting.manage',
            'finance.cash_bank',
        ],

        'cashier' => [
            'pos.terminal',
            'customers.view',
            'customers.create',
            'inventory.view',
        ],

        'warehouse' => [
            'inventory.view',
            'inventory.manage',
            'receiving.manage',
            'warehouse.manage',
            'master_data.units.view',
        ],

        'purchasing' => [
            'purchasing.view',
            'purchasing.manage',
            'purchasing.bills',
            'receiving.manage',
            'customers.view',
            'master_data.units.view',
        ],

        'accountant' => [
            'accounting.view',
            'accounting.manage',
            'finance.cash_bank',
            'purchasing.bills',
            'customers.view',
        ],
    ];
}
```

---

## ⚡ 4. Script Eksekusi Seeder Idempoten

Metode `run()` pada `RbacSeeder.php` wajib menerapkan prinsip **non-destruktif** agar aman dijalankan di database produksi:

```php
public function run(): void
{
    // 1. Buat / Perbarui master permission
    foreach ($this->permissionMatrix() as $slug => [$name, $category, $description]) {
        \App\Models\Permission::updateOrCreate(
            ['slug' => $slug],
            [
                'name'        => $name,
                'category'    => $category,
                'description' => $description,
            ]
        );
    }

    // 2. Buat system roles jika belum ada
    $systemRoles = [
        'admin'      => 'Administrator Bisnis',
        'cashier'    => 'Kasir',
        'warehouse'  => 'Staf Gudang',
        'purchasing' => 'Staf Pembelian',
        'accountant' => 'Akuntan / Keuangan',
    ];

    foreach ($systemRoles as $slug => $name) {
        $role = \App\Models\Role::firstOrCreate(
            ['slug' => $slug, 'business_id' => null],
            ['name' => $name, 'is_system' => true]
        );

        // 3. Sinkronisasikan permission ke role
        if (isset($this->rolePermissionMatrix()[$slug])) {
            $permissionIds = \App\Models\Permission::whereIn('slug', $this->rolePermissionMatrix()[$slug])
                ->pluck('id')
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}
```

Jalankan seeder kapan pun ada penambahan permission baru:
```bash
php artisan db:seed --class=RbacSeeder --force
```

---

## 🖥️ 5. Sinkronisasi ke UI Blade & Navigation Sidebar

### A. Di Navigation Registry (`app/Support/Navigation/NavigationRegistry.php`):
```php
[
    'title'      => 'Kategori Produk',
    'route'      => 'product-categories.index',
    'permission' => 'master_data.product_categories.view',
],
[
    'title'      => 'Pelanggan',
    'route'      => 'customers.index',
    'permission' => 'customers.view',
]
```

### B. Di Blade View (`resources/views/...`):
```blade
{{-- Tombol Tambah Pelanggan hanya muncul jika user memiliki permission create --}}
@if (\App\Support\Context::hasPermission('customers.create'))
    <button type="button" @click="openCreateModal = true" class="btn btn-primary">
        {{ __('customers.btn_create') }}
    </button>
@endif

{{-- Tombol Hapus Pelanggan --}}
@if (\App\Support\Context::hasPermission('customers.delete'))
    <button type="button" @click="confirmDelete(customer.id)" class="btn btn-danger-soft">
        {{ __('common.delete') }}
    </button>
@endif
```
