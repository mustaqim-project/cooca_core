# Rencana Implementasi Bertahap: Remediasi Menyeluruh Navigasi Sidebar, Sinkronisasi Collapsed Flyout, Stabilitas Interaksi & Penegakan RBAC Zero-Error

> **ID Rencana:** `PLAN-14-SIDEBAR-NAVIGATION-REMEDIATION`  
> **Target Berkas:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php), [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php)  
> **Rujukan Master:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md), [`docs/prd/PRD-14-SIDEBAR-NAVIGATION-REMEDIATION-UX-STABILITY.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-14-SIDEBAR-NAVIGATION-REMEDIATION-UX-STABILITY.md)  
> **Status:** SELESAI / COMPLETED (Seluruh 4 Fase Terverifikasi 100%)  
> **Tingkat Risiko:** *Structural & Safe Change* (Tanpa Perubahan Skema Basis Data)  

---

## 1. Ringkasan Eksekutif & Roadmap 4-Fase

Rencana implementasi ini dirancang untuk mengeksekusi perbaikan teknis hulu-ke-hilir pada shell navigasi sidebar Cooca secara terstruktur, *surgical*, dan minim risiko regresi. Seluruh perubahan berfokus pada **peningkatan stabilitas antarmuka**, **penghapusan duplikasi rute**, **paritas menu 100% antara Expanded dan Flyout**, serta **penegakan otorisasi RBAC zero-error**.

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│ FASE 1: Penegakan Otorisasi RBAC & Eliminasi Duplikasi Rute (P1 - Kritis)   │
│         - Bungkus rute feedback.bugs.index dengan guard isOwner()           │
│         - Selaraskan guard import.index dengan backend (materials & products)│
│         - Hapus duplikasi tax.index di Grup 6, konsolidasi ke Grup 7        │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ FASE 2: Sinkronisasi Paritas Collapsed Flyout & Jembatan Hover Anti-Flicker  │
│         - Tambahkan Invisible Hover Bridge (before:-left-4) di seluruh flyout│
│         - Tambahkan 4 menu Master Data Logistik ke Flyout Grup 3            │
│         - Tambahkan Simulator, Biaya Mesin, & Payroll ke Flyout Grup 6      │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ FASE 3: Penyempurnaan Ergonomi UMKM (40–65 Tahun) & Estetika Bento HIG      │
│         - Standardisasi touch target baris menu minimal 40px–44px           │
│         - Harmonisasi copy UI bahasa humanis bebas jargon membingungkan     │
│         - Penyelarasan palet warna aksen semantik Apple resmi per kategori  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ FASE 4: Pengujian Komprehensif, Verifikasi Sintaks & Dokumentasi 3-Layer    │
│         - Pengujian sintaks PHP Blade (php -l) & route list audit           │
│         - Pengujian fungsional interaktivitas Alpine.js di browser          │
│         - Pencatatan di docs/AiWorkHistory.md & pembaruan SYSTEM_GUIDE.md   │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Rincian Teknis Per Fase

### FASE 1: Penegakan Otorisasi RBAC & Eliminasi Duplikasi Rute [SELESAI / COMPLETED] (P1 - Kritis)

1. **Langkah 1.1: Proteksi Eksklusif Owner pada `feedback.bugs.index`**
   - **Berkas:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php) (baris 2286 dan baris 2341).
   - **Tindakan:** Bungkus tombol link "Bantuan & Dukungan" di mode Expanded dan mode Flyout dengan kondisional Blade `@if (\App\Support\Context::isOwner()) ... @endif`.
   - **Hasil:** Kasir dan staf operasional biasa tidak akan melihat menu pelaporan bug yang terlarang bagi mereka, mencegah error **HTTP 403 Forbidden**.

2. **Langkah 1.2: Penyelarasan Otorisasi Impor Excel (`import.index`)**
   - **Berkas:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php) (baris 1321) & [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php) (baris 177).
   - **Tindakan:**
     - Di `sidebar.blade.php`, perbarui guard menjadi:
       ```blade
       @if (\App\Support\Context::hasPermission('materials.view') || \App\Support\Context::hasPermission('products.view') || \App\Support\Context::isOwner())
       ```
     - Di `routes/owner.php`, pastikan staf pengelola produk maupun bahan baku dapat mengakses halaman impor massal.

3. **Langkah 1.3: Eliminasi Duplikasi `tax.index` & Konsolidasi ke Grup 7**
   - **Berkas:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php).
   - **Tindakan:**
     - Hapus pemanggilan `route('tax.index')` dari Grup 6 baris 1927–1932 ("Perhitungan Pajak Karyawan").
     - Di Grup 7 (baris 2114 dan 2167), standarisasi tautan menjadi *"Laporan Pajak & Kepatuhan"* dengan guard `@if (\App\Support\Context::hasPermission('reports.view') || \App\Support\Context::isOwner())`.
   - **Hasil:** Rute pajak memiliki identitas tunggal, jelas posisinya di pusat pelaporan, dan terlindungi sesuai hak akses backend.

---

### FASE 2: Sinkronisasi Paritas Collapsed Flyout & Jembatan Hover Anti-Flicker [SELESAI / COMPLETED] (P1 - Kritis)

1. **Langkah 2.1: Pemasangan *Invisible Hover Bridge* pada Semua Flyout**
   - **Berkas:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php).
   - **Tindakan:**
     - Pada setiap kontainer flyout (`overview`, `pos`, `b2bSales`, `inventory`, `purchasing`, `marketing`, `finance`, `reports`, `settings`), tambahkan kelas utilitas pseudo-elemen jembatan:
       ```html
       class="fixed left-[84px] ... before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50 ..."
       ```
   - **Hasil:** Kursor pengguna yang melintasi celah fisik 8px antara rel sidebar dan jendela flyout tidak akan memicu penutupan popover secara tiba-tiba.

2. **Langkah 2.2: Penambahan 4 Menu Master Data Logistik ke Flyout Grup 3**
   - **Berkas:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php) (dalam `activeFlyout === 'inventory'`).
   - **Tindakan:** Tambahkan subgrup Master Data Logistik:
     - Kategori Produk (`route('product-categories.index')`)
     - Kategori Bahan Baku (`route('material-categories.index')`)
     - Satuan Ukur (`route('units.index')`)
     - Impor / Ekspor Excel (`route('import.index')`)

3. **Langkah 2.3: Penambahan Menu Biaya & Payroll ke Flyout Grup 6**
   - **Berkas:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php) (dalam `activeFlyout === 'finance'`).
   - **Tindakan:**
     - Tambahkan Simulator Harga Jual (`route('simulator.index')`) dan Biaya Mesin (`route('labor-machines.index')`).
     - Tambahkan link Payroll & Slip Gaji (`route('hrm.payrolls.index')`) secara terpisah dan eksplisit di samping Data Karyawan (`route('hrm.index')`).

---

### FASE 3: Penyempurnaan Ergonomi UMKM & Estetika Bento HIG [SELESAI / COMPLETED] (P2 - Tinggi)

1. **Langkah 3.1: Harmonisasi Copywriting Bahasa Humanis**
   - Ganti istilah teoritis membingungkan dengan bahasa bisnis nyata:
     - `finance.settlements.index` ➔ **"Pencairan Dana Penjualan"**
     - `reports.index?tab=stock` ➔ **"Laporan & Valuasi Stok"**
     - `approval-rules.index` ➔ **"Aturan Persetujuan Transaksi (MAR)"**
     - `roles.index` ➔ **"Izin Akses & Peran Karyawan"**

2. **Langkah 3.2: Penerapan Warna Aksen Semantik Apple Konsisten**
   - Berikan warna aksen semantik khas Apple pada ikon:
     - POS & Dashboard: `#007AFF` (Blue)
     - Penjualan B2B: `#5856D6` (Indigo)
     - Logistik & Stok: `#FF9500` (Amber)
     - Pengadaan: `#30B0C7` (Cyan)
     - Pemasaran: `#FF2D55` (Rose)
     - Kas & Keuangan: `#34C759` (Emerald)
     - Laporan & Analitik: `#AF52DE` (Purple)
     - Pengaturan: `#8E8E93` (Slate)

3. **Langkah 3.3: Pemisahan Visual Subgrup Karyawan & Payroll**
   - Tambahkan pemisah kartu mini (`border-t border-black/5 dark:border-white/10 pt-2 mt-2`) pada subgrup Karyawan di Grup Keuangan agar tidak tenggelam di antara akun pembukuan akuntansi.

---

### FASE 4: Pengujian Komprehensif, Verifikasi & Dokumentasi [SELESAI / COMPLETED] (P1 - Wajib)

1. **Langkah 4.1: Verifikasi Sintaksis Blade & Linting**
   - Jalankan `php -l resources/views/layouts/partials/sidebar.blade.php` untuk memastikan 100% bebas dari kesalahan kompilasi PHP.
2. **Langkah 4.2: Audit Verifikasi Rute Otomatis**
   - Jalankan script audit untuk memastikan:
     - Seluruh 71 rute valid dan terdaftar di route engine Laravel.
     - 0 duplikasi rute antar-grup.
     - 100% paritas rute antara mode Expanded dan Flyout.
3. **Langkah 4.3: Pembaruan Dokumentasi 3-Layer**
   - Catat pekerjaan lengkap di [`docs/AiWorkHistory.md`](file:///c:/laragon/www/cooca_core/docs/AiWorkHistory.md).
   - Perbarui catatan arsitektur di [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md) dan [`docs/system/NAVIGATION_SYSTEM.md`](file:///c:/laragon/www/cooca_core/docs/system).

---

## 3. Rencana Pengujian & Verifikasi Kualitas (QA Acceptance Criteria)

| Skenario Pengujian | Hasil yang Diharapkan | Metode Verifikasi |
| :--- | :--- | :--- |
| **Akses Staf Biasa ke Pengaturan** | Tombol "Bantuan & Dukungan" **tidak muncul** untuk staf non-owner | Login staf kasir/admin, periksa sidebar |
| **Akses Rute Pajak** | Link "Laporan Pajak & Kepatuhan" hanya di Grup 7 dan dapat dibuka tanpa 403 | Klik link pajak dengan akun manajer laporan |
| **Navigasi Flyout Rel 76px** | Gerakkan mouse melintasi celah rel-ke-flyout secara diagonal; flyout **tetap terbuka stabil tanpa flicker** | Pengujian interaksi mouse di desktop |
| **Kelengkapan Fitur Flyout** | Menu Kategori Produk, Bahan, Satuan, Impor, Simulator, dan Payroll **tersedia & dapat diklik** di Flyout | Periksa flyout Grup 3 & Grup 6 |
| **Integritas Sintaks Blade** | File Blade terkompilasi 100% valid tanpa warning atau runtime error | Eksekusi `php -l` & `php artisan route:list` |
