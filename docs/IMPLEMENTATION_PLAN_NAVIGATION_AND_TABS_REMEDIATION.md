# Rencana Aksi & Implementasi: Remediasi Sistem Navigasi Persisten, Hierarki Breadcrumb & Tab Sekunder Bento Apple HIG

**Referensi Dokumen:** [`docs/prd/PRD-09-PERSISTENT-TABS-BREADCRUMB-NAVIGATION-REMEDIATION.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-09-PERSISTENT-TABS-BREADCRUMB-NAVIGATION-REMEDIATION.md)  
**ID Dokumen:** `PLAN-09-NAVIGATION-TABS-REMEDIATION`  
**Penanggung Jawab:** Senior Software Architect, Senior Laravel Engineer, Senior UI/UX Engineer, Security Engineer  
**Status:** READY FOR APPROVAL  

---

## 1. Ringkasan Eksekutif & Sasaran Teknis

Rencana implementasi ini dirancang untuk mengeksekusi perbaikan menyeluruh terhadap masalah **hilangnya tab sekunder**, **inversi hierarki layout**, dan **fragmentasi otorisasi** pada seluruh modul COOCA Core secara terstruktur dan terukur tanpa mengganggu fungsionalitas bisnis yang sedang berjalan.

### Sasaran Utama:
1. Membangun **Single Source of Truth (SSOT)** navigasi melalui `App\Support\Navigation\NavigationRegistry`.
2. Menyediakan shared component `<x-module-header>` dan `<x-module-tabs>` yang sepenuhnya mematuhi standar **Bento Apple HIG**.
3. Menyelesaikan masalah "Tab Hilang" pada Kategori Bahan Baku dan Kategori Produk dengan menyediakan wrapper context navigasi.
4. Menyeragamkan urutan hierarki:
   ```text
   Breadcrumb → Page Title & Actions → Persistent Secondary Tabs → Page Content
   ```
5. Memastikan proteksi rute langsung (*direct URL*) saat modul berstatus non-aktif.

---

## 2. Peta Fase Implementasi (10 Tahapan)

```text
┌────────────────────────────────────────────────────────────────────────┐
│ FASE 1: Keamanan & Hardening Middleware Modul (Direct URL Access)       │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 2: Arsitektur Single Source of Truth: NavigationRegistry.php      │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 3: Pembuatan Shared Blade Components (<x-module-header/tabs>)     │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 4: Contextual Wrapper untuk Master Data (Bahan Baku & Produk)     │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 5: Refactoring Hub Keuangan (Finance & Accounting)                │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 6: Refactoring Hub Pengadaan & Logistik (Purchasing & Inventory)  │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 7: Refactoring Hub Penjualan B2B, CRM & Toko Online               │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 8: Refactoring Hub Komunikasi (WhatsApp & Social Media)           │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 9: Pembersihan Layout Global (app.blade.php & sidebar.blade.php)  │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 10: Pengujian End-to-End, Verifikasi & AiWorkHistory.md           │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Rincian Teknis Per Fase

---

### FASE 1: Keamanan & Hardening Middleware Modul (Direct URL Access)

**Tujuan:** Mencegah akses rute langsung via URL ketika modul dinonaktifkan di *Kelola Modul* (`settings`).

**File Sasaran:**
- [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php)
- [`routes/admin.php`](file:///c:/laragon/www/cooca_core/routes/admin.php)
- [`app/Http/Middleware/CheckModuleEnabled.php`](file:///c:/laragon/www/cooca_core/app/Http/Middleware/CheckModuleEnabled.php) (jika perlu penyesuaian)

**Langkah Kerja:**
1. Audit seluruh route group:
   - `crm.*` → wajib dilindungi middleware `module:crm`
   - `purchasing.*`, `purchase-orders.*` → wajib dilindungi `module:purchasing`
   - `storefront.*` → wajib dilindungi `module:storefront`
   - `whatsapp.*` → wajib dilindungi `module:whatsapp`
   - `social-media.*` → wajib dilindungi `module:social_media`
   - `finance.accounting.*` → wajib dilindungi `module:accounting`
2. Pastikan respons saat modul nonaktif berupa redirect ke dashboard utama dengan pesan flash terstandarisasi:
   `"Modul [Nama Modul] saat ini dinonaktifkan untuk usaha Anda."`

**Kriteria Verifikasi Fase 1:**
- Matikan modul CRM via Settings.
- Ketik langsung di browser: `http://localhost/crm/members`.
- Sistem harus menolak akses (redirect/403) dan tidak merender halaman.

---

### FASE 2: Arsitektur Single Source of Truth (`NavigationRegistry.php`)

**Tujuan:** Menyediakan satu titik pusat pendefinisian seluruh modul, metadata breadcrumb, tab sekunder, rute, dan permission.

**File Sasaran:**
- **File Baru:** `app/Support/Navigation/NavigationRegistry.php`

**Spesifikasi Kelas:**
```php
namespace App\Support\Navigation;

use App\Support\Context;
use Illuminate\Support\Facades\Route;

class NavigationRegistry
{
    public static function getModules(): array
    {
        return [
            'finance' => [
                'label' => 'Keuangan',
                'parent_breadcrumb' => ['label' => 'Keuangan', 'route' => 'finance.cash-bank.index'],
                'tabs' => [
                    [
                        'key' => 'cash-bank',
                        'label' => 'Kas & Bank',
                        'route' => 'finance.cash-bank.index',
                        'active_routes' => ['finance.cash-bank.*'],
                        'permission' => 'finance.cash_bank.view',
                    ],
                    [
                        'key' => 'expenses',
                        'label' => 'Biaya Operasional',
                        'route' => 'finance.expenses.index',
                        'active_routes' => ['finance.expenses.*'],
                        'permission' => 'finance.expenses.view',
                    ],
                    [
                        'key' => 'journals',
                        'label' => 'Jurnal Umum',
                        'route' => 'finance.journals.index',
                        'active_routes' => ['finance.journals.*'],
                        'permission' => 'finance.journals.view',
                    ],
                    [
                        'key' => 'payables',
                        'label' => 'Hutang Usaha',
                        'route' => 'finance.payables.index',
                        'active_routes' => ['finance.payables.*'],
                        'permission' => 'finance.payables.view',
                    ],
                    [
                        'key' => 'receivables',
                        'label' => 'Piutang Usaha',
                        'route' => 'finance.receivables.index',
                        'active_routes' => ['finance.receivables.*'],
                        'permission' => 'finance.receivables.view',
                    ],
                    [
                        'key' => 'settlements',
                        'label' => 'Settlement Kasir',
                        'route' => 'finance.settlements.index',
                        'active_routes' => ['finance.settlements.*'],
                        'permission' => 'finance.settlements.view',
                    ],
                ],
            ],
            'accounting' => [
                'label' => 'Akuntansi',
                'parent_breadcrumb' => ['label' => 'Akuntansi & Pembukuan', 'route' => 'finance.accounting.coa'],
                'tabs' => [
                    ['key' => 'coa', 'label' => 'Bagan Akun (COA)', 'route' => 'finance.accounting.coa', 'active_routes' => ['finance.accounting.coa*'], 'permission' => 'finance.accounting.view'],
                    ['key' => 'ledger', 'label' => 'Buku Besar', 'route' => 'finance.accounting.ledger', 'active_routes' => ['finance.accounting.ledger*'], 'permission' => 'finance.accounting.view'],
                    ['key' => 'trial-balance', 'label' => 'Neraca Saldo', 'route' => 'finance.accounting.trial-balance', 'active_routes' => ['finance.accounting.trial-balance*'], 'permission' => 'finance.accounting.view'],
                    ['key' => 'reports', 'label' => 'Laporan Keuangan', 'route' => 'finance.accounting.reports', 'active_routes' => ['finance.accounting.reports*'], 'permission' => 'finance.accounting.view'],
                    ['key' => 'reconciliation', 'label' => 'Rekonsiliasi', 'route' => 'finance.accounting.reconciliation', 'active_routes' => ['finance.accounting.reconciliation*'], 'permission' => 'finance.accounting.view'],
                ],
            ],
            'purchasing' => [
                'label' => 'Pembelian & Pemasok',
                'parent_breadcrumb' => ['label' => 'Pembelian', 'route' => 'purchase-orders.index'],
                'tabs' => [
                    ['key' => 'po', 'label' => 'Pesanan Pembelian', 'route' => 'purchase-orders.index', 'active_routes' => ['purchase-orders.*'], 'permission' => 'purchasing.po.view'],
                    ['key' => 'bills', 'label' => 'Tagihan Vendor', 'route' => 'purchasing.bills.index', 'active_routes' => ['purchasing.bills.*'], 'permission' => 'purchasing.bills.view'],
                    ['key' => 'returns', 'label' => 'Retur Pembelian', 'route' => 'purchasing.returns.index', 'active_routes' => ['purchasing.returns.*'], 'permission' => 'purchasing.returns.view'],
                    ['key' => 'suppliers', 'label' => 'Pemasok', 'route' => 'suppliers.index', 'active_routes' => ['suppliers.*'], 'permission' => 'suppliers.view'],
                ],
            ],
            'products' => [
                'label' => 'Katalog Produk',
                'parent_breadcrumb' => ['label' => 'Produk', 'route' => 'products.index'],
                'tabs' => [
                    ['key' => 'catalog', 'label' => 'Katalog Produk', 'route' => 'products.index', 'active_routes' => ['products.index', 'products.show', 'products.create', 'products.edit'], 'permission' => 'products.view'],
                    ['key' => 'categories', 'label' => 'Kategori Produk', 'route' => 'products.categories.index', 'active_routes' => ['products.categories.*', 'master-data.index?type=product_category'], 'permission' => 'products.view'],
                    ['key' => 'variants', 'label' => 'Varian & Atribut', 'route' => 'products.variants.index', 'active_routes' => ['products.variants.*', 'master-data.index?type=product_variant'], 'permission' => 'products.view'],
                ],
            ],
            'materials' => [
                'label' => 'Bahan Baku',
                'parent_breadcrumb' => ['label' => 'Bahan Baku', 'route' => 'materials.index'],
                'tabs' => [
                    ['key' => 'catalog', 'label' => 'Katalog Bahan Baku', 'route' => 'materials.index', 'active_routes' => ['materials.index', 'materials.show', 'materials.create', 'materials.edit'], 'permission' => 'materials.view'],
                    ['key' => 'categories', 'label' => 'Kategori Bahan', 'route' => 'materials.categories.index', 'active_routes' => ['materials.categories.*', 'master-data.index?type=material_category'], 'permission' => 'materials.view'],
                    ['key' => 'uom', 'label' => 'Satuan Ukur (UoM)', 'route' => 'materials.uom.index', 'active_routes' => ['materials.uom.*', 'master-data.index?type=uom'], 'permission' => 'materials.view'],
                ],
            ],
            'inventory' => [
                'label' => 'Inventori & Stok',
                'parent_breadcrumb' => ['label' => 'Inventori', 'route' => 'inventory.stocks'],
                'tabs' => [
                    ['key' => 'stocks', 'label' => 'Ringkasan Stok', 'route' => 'inventory.stocks', 'active_routes' => ['inventory.stocks*'], 'permission' => 'inventory.stock.view'],
                    ['key' => 'warehouses', 'label' => 'Lokasi Gudang', 'route' => 'warehouse.index', 'active_routes' => ['warehouse.*'], 'permission' => 'inventory.warehouse.view'],
                ],
            ],
            'sales' => [
                'label' => 'Penjualan B2B',
                'parent_breadcrumb' => ['label' => 'Penjualan B2B', 'route' => 'sales-orders.index'],
                'tabs' => [
                    ['key' => 'orders', 'label' => 'Pesanan Penjualan (SO)', 'route' => 'sales-orders.index', 'active_routes' => ['sales-orders.*'], 'permission' => 'sales.orders.view'],
                    ['key' => 'quotations', 'label' => 'Surat Penawaran', 'route' => 'quotations.index', 'active_routes' => ['quotations.*'], 'permission' => 'sales.quotations.view'],
                    ['key' => 'invoices', 'label' => 'Faktur Tagihan', 'route' => 'invoices.index', 'active_routes' => ['invoices.*'], 'permission' => 'sales.invoices.view'],
                    ['key' => 'returns', 'label' => 'Retur Penjualan', 'route' => 'sales.returns.index', 'active_routes' => ['sales.returns.*'], 'permission' => 'sales.returns.view'],
                ],
            ],
            'crm' => [
                'label' => 'Pelanggan & CRM',
                'parent_breadcrumb' => ['label' => 'Pelanggan & CRM', 'route' => 'customers.index'],
                'tabs' => [
                    ['key' => 'customers', 'label' => 'Daftar Pelanggan', 'route' => 'customers.index', 'active_routes' => ['customers.*'], 'permission' => 'crm.customers.view'],
                    ['key' => 'members', 'label' => 'Member & Tingkatan', 'route' => 'crm.members', 'active_routes' => ['crm.members*'], 'permission' => 'crm.members.view'],
                    ['key' => 'vouchers', 'label' => 'Voucher Promo', 'route' => 'crm.vouchers', 'active_routes' => ['crm.vouchers*'], 'permission' => 'crm.vouchers.view'],
                ],
            ],
            'communication' => [
                'label' => 'Komunikasi & Saluran',
                'parent_breadcrumb' => ['label' => 'Pusat Komunikasi', 'route' => 'whatsapp.index'],
                'tabs' => [
                    ['key' => 'whatsapp', 'label' => 'WhatsApp Inbox', 'route' => 'whatsapp.index', 'active_routes' => ['whatsapp.index*'], 'permission' => 'communication.whatsapp.view'],
                    ['key' => 'broadcast', 'label' => 'WhatsApp Broadcast', 'route' => 'whatsapp.broadcast', 'active_routes' => ['whatsapp.broadcast*'], 'permission' => 'communication.whatsapp.view'],
                    ['key' => 'social', 'label' => 'Media Sosial', 'route' => 'social-media.index', 'active_routes' => ['social-media.*'], 'permission' => 'communication.social.view'],
                ],
            ],
        ];
    }
}
```

**Kriteria Verifikasi Fase 2:**
- Unit test sederhana / dump: `NavigationRegistry::getModules()` mengembalikan array valid tanpa syntax error.

---

### FASE 3: Pembuatan Shared Blade Components

**Tujuan:** Standarisasi visual breadcrumb, page header, action button, dan baris tab Bento Apple HIG.

**File Sasaran:**
- **File Baru:** `resources/views/components/module-header.blade.php`
- **File Baru:** `resources/views/components/module-tabs.blade.php`

**Spesifikasi Desain Komponen:**
1. `<x-module-header>`:
   - Render breadcrumb di baris teratas:
     `Home / [Parent Module Link] / [Current Page Leaf]`
   - Render judul `h1` dengan typography Apple HIG (`text-2xl font-bold tracking-tight text-black`).
   - Slot kanan untuk Action Buttons (misal: "Tambah Bahan", "Export", "Filter").
2. `<x-module-tabs>`:
   - Mengambil konfigurasi modul dari `NavigationRegistry`.
   - Filter tab yang diizinkan berdasarkan user permission (`Context::hasPermission(...)` / Gate).
   - Container scrollable horizontal:
     `flex items-center gap-1.5 p-1 bg-black/[0.04] rounded-2xl overflow-x-auto scrollbar-none`
   - Tab aktif: `bg-white text-black font-semibold shadow-xs`
   - Tab inaktif: `text-black/60 hover:text-black hover:bg-white/50 font-medium`

**Kriteria Verifikasi Fase 3:**
- Komponen dapat di-render tanpa error pada view pengujian lokal.

---

### FASE 4: Contextual Wrapper untuk Master Data (Bahan Baku & Produk)

**Tujuan:** Menyelesaikan akar masalah hilangnya tab saat mengklik "Kategori Bahan" atau "Kategori Produk".

**File Sasaran:**
- [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php)
- **File Baru:** `resources/views/app/materials/categories.blade.php`
- **File Baru:** `resources/views/app/materials/uom.blade.php`
- **File Baru:** `resources/views/app/products/categories.blade.php`
- **File Baru:** `resources/views/app/products/variants.blade.php`
- [`app/Http/Controllers/MasterDataController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/MasterDataController.php) (opsional delegasi view)

**Langkah Kerja:**
1. Definisikan route alias dengan nama resmi:
   - `materials.categories.index` → render view `app/materials/categories` dengan parameter type `material_category`.
   - `materials.uom.index` → render view `app/materials/uom` dengan parameter type `uom`.
   - `products.categories.index` → render view `app/products/categories` dengan parameter type `product_category`.
   - `products.variants.index` → render view `app/products/variants` dengan parameter type `product_variant`.
2. Pada view-view baru tersebut:
   - Pasang `<x-module-header module="materials" ...>`
   - Pasang `<x-module-tabs module="materials" />`
   - `@include('app.master-data.partials.content', ['type' => 'material_category'])` (atau memuat form/tabel master data terkait).

**Kriteria Verifikasi Fase 4:**
- Klik *Katalog Bahan Baku* → tab muncul.
- Klik *Kategori Bahan* → URL berganti ke kategori bahan, dan baris tab **tetap muncul utuh** dengan active tab berpindah ke *Kategori Bahan*.
- Klik *Satuan Ukur* → baris tab **tetap muncul utuh** dengan active tab berpindah ke *Satuan Ukur*.

---

### FASE 5: Refactoring Hub Keuangan (Finance & Accounting)

**Tujuan:** Menyeragamkan urutan hierarki dan persistent tabs di seluruh halaman keuangan dan akuntansi.

**File Sasaran:**
- [`resources/views/app/finance/cash-bank/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/cash-bank/index.blade.php)
- [`resources/views/app/finance/cash-bank/ledger.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/cash-bank/ledger.blade.php)
- [`resources/views/app/finance/expenses.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/expenses.blade.php)
- [`resources/views/app/finance/journals.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/journals.blade.php)
- [`resources/views/app/finance/payables.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/payables.blade.php)
- [`resources/views/app/finance/receivables.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/receivables.blade.php)
- [`resources/views/app/finance/settlements/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/settlements/index.blade.php)
- [`resources/views/app/finance/accounting/coa.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/accounting/coa.blade.php)
- [`resources/views/app/finance/accounting/ledger.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/accounting/ledger.blade.php)
- [`resources/views/app/finance/accounting/trial-balance.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/accounting/trial-balance.blade.php)

**Langkah Kerja:**
1. Hapus markup hardcoded tab navigasi di setiap file tersebut.
2. Pasang `<x-module-header>` dan `<x-module-tabs module="finance">` pada 7 file Finance Hub.
3. Pasang `<x-module-header>` dan `<x-module-tabs module="accounting">` pada file-file Accounting Hub.
4. Pastikan styling dan responsiveness seragam.

**Kriteria Verifikasi Fase 5:**
- Navigasi Kas & Bank → Biaya Operasional → Jurnal Umum → Hutang → Piutang → Settlement berjalan mulus dengan koleksi tab tetap identik.

---

### FASE 6: Refactoring Hub Pengadaan & Logistik (Purchasing, Products, Materials, Warehouse)

**Tujuan:** Memperbaiki inversi visual (tab di atas breadcrumb) dan menyatukan navigasi logistik.

**File Sasaran:**
- [`resources/views/app/purchase-orders/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php)
- [`resources/views/app/purchasing/bills/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchasing/bills/index.blade.php)
- [`resources/views/app/purchasing/returns/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchasing/returns/index.blade.php)
- [`resources/views/app/suppliers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/suppliers/index.blade.php)
- [`resources/views/app/products/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/products/index.blade.php)
- [`resources/views/app/materials/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/materials/index.blade.php)
- [`resources/views/app/inventory/stocks.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/inventory/stocks.blade.php)
- [`resources/views/app/warehouse/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/warehouse/index.blade.php)
- [`resources/views/app/warehouse/show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/warehouse/show.blade.php)

**Langkah Kerja:**
1. Balik susunan tata letak: letakkan Breadcrumb di atas, Page Title di tengah, dan Tab di bawah Title.
2. Terapkan `<x-module-tabs module="purchasing">` pada PO, Bills, Returns, dan Suppliers.
3. Terapkan `<x-module-tabs module="products">` pada Katalog Produk.
4. Terapkan `<x-module-tabs module="materials">` pada Katalog Bahan Baku.
5. Terapkan `<x-module-tabs module="inventory">` pada Ringkasan Stok dan Gudang.
6. Pada `warehouse/show.blade.php`, jadikan breadcrumb: `Home / Inventori / Lokasi Gudang / [Nama Gudang]` dan kembalikan konteks detail page yang bersih.

**Kriteria Verifikasi Fase 6:**
- Pindah rute di antara 4 halaman Purchasing mempertahankan koleksi 4 tab lengkap.
- Hierarki visual tidak lagi terbalik.

---

### FASE 7: Refactoring Hub Penjualan B2B, CRM & Toko Online

**Tujuan:** Mengkonsolidasikan tab penjualan B2B, menyatukan dualisme pelanggan vs CRM, dan merapikan pengaturan toko online.

**File Sasaran:**
- [`resources/views/app/sales-orders/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/sales-orders/index.blade.php)
- [`resources/views/app/quotations/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/quotations/index.blade.php)
- [`resources/views/app/invoices/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/invoices/index.blade.php)
- [`resources/views/app/invoices/show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/invoices/show.blade.php)
- [`resources/views/app/sales/returns/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/sales/returns/index.blade.php)
- [`resources/views/app/customers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/customers/index.blade.php)
- [`resources/views/app/crm/members.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/crm/members.blade.php)
- [`resources/views/app/crm/vouchers.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/crm/vouchers.blade.php)
- [`resources/views/app/storefront/settings.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/storefront/settings.blade.php)

**Langkah Kerja:**
1. Standarisasi B2B Sales dengan tab: `[Pesanan Penjualan (SO)] [Surat Penawaran] [Faktur Tagihan] [Retur Penjualan]`.
2. Satukan modul CRM & Pelanggan:
   - Ganti tab Alpine lokal pada `customers/index.blade.php` dengan link HTTP tab yang sinkron dengan `crm.members` dan `crm.vouchers`.
   - Pasang `<x-module-tabs module="crm">` pada ketiga view.
3. Selaraskan `storefront/settings.blade.php` dengan standard header dan breadcrumb.

**Kriteria Verifikasi Fase 7:**
- Membuka Faktur Penjualan tetap memperlihatkan tab SO, Quotation, dan Retur.
- Berpindah antara Daftar Pelanggan, Member, dan Voucher mengubah URL rute secara benar dengan koleksi tab tetap utuh.

---

### FASE 8: Refactoring Hub Komunikasi (WhatsApp & Social Media)

**Tujuan:** Menyatukan kanal komunikasi omnichannel dalam satu koleksi navigasi persisten.

**File Sasaran:**
- [`resources/views/app/whatsapp/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/whatsapp/index.blade.php)
- [`resources/views/app/whatsapp/broadcast.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/whatsapp/broadcast.blade.php)
- [`resources/views/app/social_media/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/social_media/index.blade.php)

**Langkah Kerja:**
1. Pasang `<x-module-header module="communication" ...>`
2. Pasang `<x-module-tabs module="communication" />` pada ketiga halaman.

**Kriteria Verifikasi Fase 8:**
- Berpindah dari WhatsApp Inbox ke WhatsApp Broadcast dan Social Media mempertahankan 3 tab tersebut secara persisten.

---

### FASE 9: Pembersihan Layout Global (`app.blade.php` & `sidebar.blade.php`)

**Tujuan:** Menghilangkan kode otorisasi redundan dan memastikan sidebar mengonsumsi konfigurasi dari `NavigationRegistry`.

**File Sasaran:**
- [`resources/views/layouts/app.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/app.blade.php)
- [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php)

**Langkah Kerja:**
1. Hapus blok perhitungan manual variabel otorisasi menu di `app.blade.php` (baris 27-48).
2. Manfaatkan helper atau View Composer berbasis `NavigationRegistry` sehingga status akses menu dihitung sekali secara konsisten.
3. Pastikan penanda menu aktif di sidebar selaras dengan modul aktif saat ini.

**Kriteria Verifikasi Fase 9:**
- Tidak ada variabel yang ditimpa (overwritten) secara tidak semestinya antara `app.blade.php` dan `sidebar.blade.php`.
- Sidebar tetap terbuka pada grup yang sesuai saat berada di sub-halaman/tab terkait.

---

### FASE 10: Pengujian Menyeluruh, Verifikasi & AiWorkHistory.md

**Tujuan:** Menjalankan acceptance test lengkap, memverifikasi ketiadaan regresi, dan mencatat riwayat pekerjaan.

**Aktivitas Pengujian:**
1. **Navigasi Sekunder & Persistensi Tab:**
   - Uji transisi: Tab A → Tab B → Tab C → Tab D. Koleksi tab tidak boleh berubah.
2. **Refresh & Direct URL:**
   - Refresh browser pada setiap tab. Status tab aktif harus bertahan.
   - Buka URL rute secara langsung di tab browser baru. Tab aktif terpilih dengan benar.
3. **Browser History (Back / Forward):**
   - Tekan tombol Back browser, lalu Forward. Pastikan active tab menyesuaikan dengan URL terkini.
4. **Pembatasan Hak Akses (Permission Test):**
   - Login dengan role terbatas (misal: Kasir atau Staf Gudang). Pastikan tab yang tidak diizinkan tidak muncul di DOM.
5. **Enforcement Status Modul (Module ON/OFF):**
   - Nonaktifkan modul via Kelola Modul, verifikasi menu sidebar disembunyikan dan URL langsung diblokir.
6. **Responsive Mobile Testing:**
   - Periksa tampilan pada viewport 375px (iPhone SE) dan 414px. Tab harus dapat digeser horizontal dengan rapi tanpa pembungkusan vertikal.
7. **Pencatatan Dokumentasi:**
   - Buat entri komprehensif di [`docs/AiWorkHistory.md`](file:///c:/laragon/www/cooca_core/docs/AiWorkHistory.md) sesuai standar changelog proyek.

---

## 4. Matriks Risiko & Rencana Kontinjensi

| Potensi Risiko | Tingkat | Dampak | Rencana Mitigasi / Kontinjensi |
| :--- | :---: | :--- | :--- |
| **Kehilangan State Form / Parameter Query** (misal filter pencarian di tabel saat beralih tab) | Sedang | Filter reset saat tab berganti. | Pertahankan query parameter default pada tautan tab jika diperlukan, atau simpan preferensi filter per modul di session. |
| **Konflik Nama Rute Alias** | Rendah | Error RouteNotFoundException. | Daftarkan nama rute alias secara eksplisit di `routes/owner.php` sebelum memanggilnya di komponen Blade. |
| **Over-Restrictive Module Middleware** | Sedang | Pengguna owner terblokir dari fitur yang seharusnya aktif. | Verifikasi integrasi `ModuleRegistry::isActive()` terhadap tenant context saat ini sebelum memberlakukan redirect 403. |

---

## 5. Kriteria Selesai (Definition of Done - DoD)

Pekerjaan dinyatakan selesai secara tuntas apabila:
- [ ] Seluruh tab sekunder di 10 modul hub tidak pernah hilang saat berpindah ke child rute.
- [ ] Tidak ada lagi tab navigasi yang berada di atas breadcrumb atau page title.
- [ ] Kategori bahan baku dan kategori produk memiliki context wrapper yang mempertahankan tab induknya.
- [ ] Rute terlindungi secara aman saat modul berstatus non-aktif.
- [ ] Desain konsisten dengan tema Bento Apple HIG dan responsif di perangkat mobile.
- [ ] Seluruh skenario acceptance test lolos verifikasi.
- [ ] Laporan akhir diverifikasi dan dicatat di `docs/AiWorkHistory.md`.

---
*Dokumen ini merupakan rencana kerja teknis resmi untuk eksekusi remediasi navigasi COOCA Core.*
