# PRD-09: Sistem Navigasi Persisten, Hierarki Breadcrumb & Remediasi Tab Sekunder Bento Apple HIG

**ID Dokumen:** `PRD-09-PERSISTENT-TABS-BREADCRUMB-NAVIGATION`  
**Modul:** Shell Navigasi, Hierarki Breadcrumb, Persistent Secondary Tabs & Otorisasi Modul  
**Penanggung Jawab:** Senior Software Architect, Senior Laravel Engineer, Senior UI/UX Engineer, Security Engineer  
**Status:** READY FOR APPROVAL & IMPLEMENTATION  
**Target Pengguna:** Seluruh Peran (Owner, Administrator, Finance Manager, Purchasing Staff, Warehouseman, Sales, Kasir)

---

## 1. Latar Belakang & Audit Sistem Existing (As-Is State)

### A. Permasalahan Utama
Pada arsitektur navigasi existing di repositori COOCA, terdapat anomali struktural pada baris **tab navigasi sekunder di bawah breadcrumb**:
1. **Tab Hilang Saat Berpindah Halaman (Lost Secondary Navigation):**
   Ketika user membuka *Katalog Bahan Baku* (`materials.index`), user melihat 3 tab: `[Katalog Bahan Baku] [Kategori Bahan] [Satuan Ukur]`. Namun ketika user mengklik tab *Kategori Bahan*, rute me-redirect ke `material-categories.index` yang me-render view generik [`resources/views/app/master-data/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/master-data/index.blade.php). View tersebut **sama sekali tidak memiliki tab navigation**, sehingga koleksi tab lenyap seketika. Hal identik terjadi pada *Kategori Produk* di modul Produk.
2. **Inversi Hierarki Visual Layout (Upside-Down Navigation Flow):**
   Pada sejumlah modul (`products`, `materials`, `inventory`, `sales-orders`, `quotations`, `invoices`, `sales/returns`), baris tab navigasi diletakkan di posisi paling atas halaman (sebelum Page Title dan Breadcrumb). Sementara pada modul lain (`finance`, `purchasing`, `whatsapp`), tab diletakkan di bawah Header/Breadcrumb. Hal ini melanggar kaidah hierarki visual:
   ```text
   HIRARKI SALAH (Existing):
   [TAB A] [TAB B] [TAB C]
   Title: Halaman B
   Home / Modul / B

   HIRARKI BENAR (Target):
   Home / Modul / Halaman B   (Breadcrumb)
   Title & Actions            (Header)
   [A] [B ACTIVE] [C] [D]     (Persistent Secondary Tabs)
   Page Content               (Bento Container)
   ```
3. **Ketiadaan Single Source of Truth (SSOT):**
   Tidak ada registry navigasi terpusat. Setiap file Blade menulis tag `<nav>`, `<a>`, dan kondisi active route `@if(request()->routeIs(...))` secara hardcoded. Akibatnya, pemeliharaan sangat rapuh dan rawan inkonsistensi.
4. **Duplikasi Otorisasi Menu di Dua Tempat:**
   File [`resources/views/layouts/app.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/app.blade.php) menghitung status akses menu (`$canAccessPurchasing`, `$canAccessFinance`, dll.), namun [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php) menghitung ulang dan menimpa variabel tersebut dengan logika yang berbeda.
5. **Celah Direct URL saat Modul Nonaktif:**
   Ketika modul dinonaktifkan di *Kelola Modul* (`settings`), menu disembunyikan dari sidebar, tetapi beberapa rute masih dapat diakses langsung melalui URL jika user memiliki permission dasar karena ketiadaan middleware proteksi modul terpusat.

---

## 2. Prinsip Arsitektur & Aturan Desain (To-Be State)

### A. Prinsip "One Module, One Navigation Context, Persistent Collection"
1. **Tab adalah secondary navigation milik Modul, bukan milik Halaman perorangan.**
2. Jika sebuah modul terdiri atas tab A, B, C, dan D:
   - Membuka halaman A harus menampilkan koleksi tab `[A ACTIVE] [B] [C] [D]`.
   - Membuka halaman B harus menampilkan koleksi tab `[A] [B ACTIVE] [C] [D]`.
   - Membuka halaman C harus menampilkan koleksi tab `[A] [B] [C ACTIVE] [D]`.
   - Membuka halaman D harus menampilkan koleksi tab `[A] [B] [C] [D ACTIVE]`.
   - **Koleksi tab tidak boleh berubah, mengecil, atau hilang saat navigasi internal modul terjadi.**
3. **Pembedaan Halaman Detail vs Tab Navigasi:**
   Halaman Action/Detail (seperti *Create Invoice*, *Show Purchase Order*, *Edit Warehouse*) adalah turunan leaf dari breadcrumb dan **bukan** merupakan tab baru:
   ```text
   Breadcrumb: Home / Pembelian / Pesanan Pembelian / Buat Baru
   Context: Tetap berada di Modul Purchasing
   ```

### B. Hierarki Visual Layout Bento Apple HIG
Urutan elemen dari atas ke bawah pada setiap halaman aplikasi:
```text
┌───────────────────────────────────────────────────────────────┐
│ Topbar (Global Breadcrumbs Context, Search Ctrl+K, User)      │
├───────────────────────────────────────────────────────────────┤
│ Breadcrumb Modul: Home / [Modul Induk] / [Nama Halaman Aktif] │
│                                                               │
│ Judul Halaman (H1)                    [Tombol Aksi Utama]     │
│ Sub-deskripsi singkat                                         │
├───────────────────────────────────────────────────────────────┤
│ [Tab A]  [Tab B (Aktif)]  [Tab C]  [Tab D]   (Scroll Mobile) │
├───────────────────────────────────────────────────────────────┤
│ Konten Halaman (Bento Cards, Table, Filter, Form)             │
└───────────────────────────────────────────────────────────────┘
```

---

## 3. Spesifikasi Arsitektur Teknis

### A. Single Source of Truth: `App\Support\Navigation\NavigationRegistry`
Dibuat class statis terpusat untuk mendaftarkan seluruh modul, item tab, rute target, permission, dan status modul:

```php
namespace App\Support\Navigation;

class NavigationRegistry
{
    /**
     * Mendapatkan seluruh definisi modul dan tab sekunder.
     */
    public static function all(): array;

    /**
     * Mendapatkan konfigurasi tab untuk modul tertentu.
     */
    public static function getModuleTabs(string $moduleKey): array;

    /**
     * Menentukan modul dan tab aktif berdasarkan rute saat ini.
     */
    public static function getContextForCurrentRoute(): array;

    /**
     * Memeriksa apakah user berhak melihat tab tertentu.
     */
    public static function userCanAccessTab(array $tabConfig): bool;
}
```

### B. Komponen Blade Bersama (Shared Components)

#### 1. `<x-module-header>`
Menyediakan tata letak breadcrumb, judul halaman, deskripsi, dan tombol aksi yang konsisten:
- **Atribut:**
  - `module` (string): Kunci modul (misal: `'finance'`, `'purchasing'`)
  - `title` (string): Judul halaman (H1)
  - `subtitle` (string, opsional): Keterangan ringkas halaman
  - `breadcrumbs` (array): Pasangan `label => url`
- **Output:** Menampilkan breadcrumb neutral Apple HIG (`text-black/50 text-[12px]`) di bagian atas, diikuti header row dengan flex layout yang rapi.

#### 2. `<x-module-tabs>`
Merender koleksi tab persisten modul dengan filter otorisasi dan responsivitas:
- **Atribut:**
  - `module` (string, opsional - otomatis terdeteksi via rute jika dikosongkan)
- **Karakteristik UI/UX:**
  - Menggunakan gaya Bento Apple HIG: background pill netral lembut (`bg-black/[0.04] p-1 rounded-xl`).
  - Active Tab: Background putih murni (`bg-white shadow-sm text-black font-semibold`).
  - Inactive Tab: Text slate lembut (`text-black/60 hover:text-black font-medium hover:bg-white/50`).
  - Kontainer Responsif: `overflow-x-auto flex-nowrap scrollbar-none` agar dapat digeser mulus di layar smartphone tanpa patah ke baris baru.

---

## 4. Matriks Spesifikasi Modul & Tab Sekunder

Berikut adalah daftar tab koleksi persisten untuk setiap modul utama sistem:

### 1. Finance Hub (`module="finance"`)
- **Breadcrumb Parent:** `Keuangan`
- **Tab Collection:**
  1. **Kas & Bank**: Route `finance.cash-bank.index` (Perm: `finance.cash_bank.view`)
  2. **Biaya Operasional**: Route `finance.expenses.index` (Perm: `finance.expenses.view`)
  3. **Jurnal Umum**: Route `finance.journals.index` (Perm: `finance.journals.view`)
  4. **Hutang Usaha (AP)**: Route `finance.payables.index` (Perm: `finance.payables.view`)
  5. **Piutang Usaha (AR)**: Route `finance.receivables.index` (Perm: `finance.receivables.view`)
  6. **Settlement Kasir**: Route `finance.settlements.index` (Perm: `finance.settlements.view`)

### 2. Accounting Hub (`module="accounting"`)
- **Breadcrumb Parent:** `Akuntansi & Pembukuan`
- **Tab Collection:**
  1. **Bagan Akun (COA)**: Route `finance.accounting.coa` (Perm: `finance.accounting.view`)
  2. **Buku Besar**: Route `finance.accounting.ledger` (Perm: `finance.accounting.view`)
  3. **Neraca Saldo**: Route `finance.accounting.trial-balance` (Perm: `finance.accounting.view`)
  4. **Laporan Keuangan**: Route `finance.accounting.reports` (Perm: `finance.accounting.view`)
  5. **Rekonsiliasi**: Route `finance.accounting.reconciliation` (Perm: `finance.accounting.view`)

### 3. Purchasing Hub (`module="purchasing"`)
- **Breadcrumb Parent:** `Pembelian & Supplier`
- **Tab Collection:**
  1. **Pesanan Pembelian (PO)**: Route `purchase-orders.index` (Perm: `purchasing.po.view`)
  2. **Tagihan Vendor (Bills)**: Route `purchasing.bills.index` (Perm: `purchasing.bills.view`)
  3. **Retur Pembelian**: Route `purchasing.returns.index` (Perm: `purchasing.returns.view`)
  4. **Katalog Pemasok**: Route `suppliers.index` (Perm: `suppliers.view`)

### 4. Product Catalog Hub (`module="products"`)
- **Breadcrumb Parent:** `Produk & Layanan`
- **Tab Collection:**
  1. **Katalog Produk**: Route `products.index` (Perm: `products.view`)
  2. **Kategori Produk**: Route `product-categories.index` (Perm: `products.view`)
  3. **Varian & Atribut**: Route `product-variants.index` (Perm: `products.view`)

### 5. Raw Material Hub (`module="materials"`)
- **Breadcrumb Parent:** `Bahan Baku & Resep`
- **Tab Collection:**
  1. **Katalog Bahan Baku**: Route `materials.index` (Perm: `materials.view`)
  2. **Kategori Bahan**: Route `material-categories.index` (Perm: `materials.view`)
  3. **Satuan Ukur (UoM)**: Route `uom.index` (Perm: `materials.view`)

### 6. Inventory & Logistik Hub (`module="inventory"`)
- **Breadcrumb Parent:** `Inventori & Stok`
- **Tab Collection:**
  1. **Ringkasan Stok**: Route `inventory.stocks` (Perm: `inventory.stock.view`)
  2. **Lokasi Gudang**: Route `warehouse.index` (Perm: `inventory.warehouse.view`)

### 7. B2B Sales Hub (`module="sales"`)
- **Breadcrumb Parent:** `Penjualan B2B`
- **Tab Collection:**
  1. **Pesanan Penjualan (SO)**: Route `sales-orders.index` (Perm: `sales.orders.view`)
  2. **Surat Penawaran**: Route `quotations.index` (Perm: `sales.quotations.view`)
  3. **Faktur Penjualan**: Route `invoices.index` (Perm: `sales.invoices.view`)
  4. **Retur Penjualan**: Route `sales.returns.index` (Perm: `sales.returns.view`)

### 8. CRM & Pelanggan Hub (`module="crm"`)
- **Breadcrumb Parent:** `Pelanggan & CRM`
- **Tab Collection:**
  1. **Buku Pelanggan**: Route `customers.index` (Perm: `crm.customers.view`)
  2. **Member & Tingkatan**: Route `crm.members` (Perm: `crm.members.view`)
  3. **Voucher Promo**: Route `crm.vouchers` (Perm: `crm.vouchers.view`)

### 9. Komunikasi Hub (`module="communication"`)
- **Breadcrumb Parent:** `Pusat Komunikasi`
- **Tab Collection:**
  1. **WhatsApp Chat & Inbox**: Route `whatsapp.index` (Perm: `communication.whatsapp.view`)
  2. **WhatsApp Broadcast**: Route `whatsapp.broadcast` (Perm: `communication.whatsapp.view`)
  3. **Multi-Channel Media Sosial**: Route `social-media.index` (Perm: `communication.social.view`)

### 10. Toko Online Setup Hub (`module="storefront"`)
- **Breadcrumb Parent:** `Toko Online`
- **Tab Collection (Settings Sub-Hub):**
  1. **Pengaturan Umum**: Route `storefront.settings` (tab: `general`)
  2. **Pengiriman & Kurir**: Route `storefront.settings` (tab: `fulfillment`)
  3. **Pembayaran & Kas**: Route `storefront.settings` (tab: `payment`)
  4. **Jam Operasional**: Route `storefront.settings` (tab: `schedule`)
  5. **Fitur Ekstra**: Route `storefront.settings` (tab: `features`)

---

## 5. Matriks Keamanan & Otorisasi

| Level Proteksi | Mekanisme | Perilaku Saat Akses Ditolak |
| :--- | :--- | :--- |
| **Module Level** | Middleware `module:{slug}` | Me-redirect ke dashboard utama dengan notifikasi peringatan: *"Modul tidak aktif untuk usaha Anda."* |
| **Route Level** | Middleware `permission:{slug}` | Mengembalikan `403 Forbidden` standar dengan tampilan ramah pengguna. |
| **Tab/UI Level** | Method `NavigationRegistry::userCanAccessTab` | Tab tidak dirender sama sekali di antarmuka (DOM bersih dari elemen tanpa izin). |
| **API Endpoints** | Controller FormRequest / Policy | Response JSON `{ success: false, message: 'Unauthorized' }` dengan status code 403. |

---

## 6. Rencana Implementasi Bertahap (10 Fase)

```text
FASE 1: Keamanan & Middleware Proteksi Modul pada Rute
FASE 2: Pembuatan Core NavigationRegistry (SSOT)
FASE 3: Pembuatan Shared Blade Components (<x-module-header> & <x-module-tabs>)
FASE 4: Refactor Sidebar & Topbar Konsumsi Registry
FASE 5: Implementasi Modul Keuangan (Finance & Accounting Hub)
FASE 6: Implementasi Modul Pengadaan & Inventori (Purchasing, Products, Materials, Warehouse)
FASE 7: Implementasi Modul Penjualan B2B, CRM & Toko Online
FASE 8: Implementasi Modul Komunikasi (WhatsApp & Social Media)
FASE 9: Pengujian End-to-End, Direct URL, Refresh, & Responsive Check
FASE 10: Dokumentasi Lengkap & Update AiWorkHistory.md
```

---

## 7. Kriteria Penerimaan (Acceptance Criteria)

### AC-1: Persistensi Tab Koleksi
- **Given** User berada di Modul Raw Material pada halaman *Katalog Bahan*
- **When** User mengklik tab *Kategori Bahan*
- **Then** Halaman berpindah ke rute kategori bahan, dan seluruh baris tab (`[Katalog Bahan Baku] [Kategori Bahan AKTIF] [Satuan Ukur]`) **tetap terlihat utuh**.

### AC-2: Hierarki Visual Layout
- **Given** Halaman modul apa pun dibuka
- **Then** Breadcrumb berada di baris paling atas, diikuti Page Title dan Action Button, kemudian baris Tab Sekunder, dan diakhiri dengan Bento Card konten halaman.

### AC-3: Mobile Horizontal Scroll
- **Given** User membuka modul dengan tab banyak (misal: Finance Hub) di smartphone (< 640px)
- **Then** Tab tidak membungkus vertikal menjadi beberapa baris bertumpuk, melainkan dapat digulir horizontal secara mulus dengan active tab berada dalam viewport.

### AC-4: Proteksi Module Nonaktif
- **Given** Modul CRM dinonaktifkan di *Kelola Modul*
- **When** User mencoba mengakses `https://cooca.id/crm/members` secara langsung via URL
- **Then** Sistem menolak akses dan me-redirect dengan pesan bahwa modul sedang dinonaktifkan.

---
Dokumen ini menjadi acuan spesifikasi resmi untuk pekerjaan perbaikan arsitektur navigasi COOCA Core.
