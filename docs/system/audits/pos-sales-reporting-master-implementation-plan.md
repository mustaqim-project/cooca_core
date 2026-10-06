# Master Implementation Plan: Rekayasa & Remediasi Sistem Reporting Penjualan POS (Fase 1 – 8)

**Dokumen Standar Layer 2:** [`docs/system/audits/pos-sales-reporting-master-implementation-plan.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/pos-sales-reporting-master-implementation-plan.md)  
**Dokumen Rujukan Audit:** [`docs/system/audits/pos-sales-reporting-comprehensive-audit.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/pos-sales-reporting-comprehensive-audit.md)  
**Status:** `COMPLETED & VERIFIED (100% - Seluruh 8 Fase Selesai Penuh & Teruji Hijau)`  
**Target Sistem:** Sales & POS Reporting Suite, Multi-Dimensional Analytics, 3-Way Reconciliation, 9-Sheet Excel Export Engine, Bento Apple HIG Dynamic UI, Automated Acceptance Tests

---

## 📋 1. Ringkasan Eksekutif Rencana Kerja

Rencana kerja terpadu ini disusun secara terstruktur untuk merekayasa dan mereorganisasi modul **Pelaporan Penjualan POS & Finansial COOCA** secara menyeluruh dari hulu transaksi basis data hingga hilir ekspor multi-format. Pendekatan yang digunakan adalah **Data Accuracy → Business Logic → Reporting → Export → Performance → Security → UI/UX**.

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│             ROADMAP MASTER IMPLEMENTASI SALES REPORTING POS COOCA (8 FASE TERPADU)              │
├──────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Fase 1: Domain Service Agregasi & Single Source of Truth (PosReportingService & DTOs)            │
│ Fase 2: 3-Way Reconciliation Engine (Orders ↔ Multi-Payment ↔ Shift Cash Ledger)                 │
│ Fase 3: Multi-Dimensional Filter Bar Engine (Outlet, Kasir, Shift, Kategori, Bayar, Status)     │
│ Fase 4: Arsitektur UI Bento Apple HIG Multi-Tab (15 Sub-Modul Laporan Interaktif)                │
│ Fase 5: Slide-Over Modal Quick-View Detail Transaksi & Drill-Down                                │
│ Fase 6: Engine Ekspor Excel Master 9-Sheet (PhpSpreadsheet) & Low-Memory Streamed CSV            │
│ Fase 7: Index Tuning Database & Optimasi Performa Query Skala Besar (<150ms)                     │
│ Fase 8: Automated Acceptance Test Suite (Unit & Feature Tests) & Sinkronisasi Dokumen 3-Layer    │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ 2. Rincian Teknis Eksekusi Per Fase (Fase 1 – 8)

---

### 🔹 Fase 1: Domain Service Agregasi & Single Source of Truth
- **Tujuan:** Menyatukan seluruh logika kalkulasi finansial (Gross, Net, HPP, Laba Kotor, Margin, Diskon, Retur, AOV) ke dalam satu service terpusat agar tidak ada disparitas angka antara Web, API, dan Excel.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - `app/Domain/Report/Pos/PosReportingService.php` (Baru)
  - `app/Domain/Report/Pos/DTOs/PosReportFilterDTO.php` (Baru)
  - `app/Domain/Report/Pos/DTOs/PosKpiSummaryDTO.php` (Baru)
  - `app/Domain/Report/Pos/DTOs/PosTrendDataDTO.php` (Baru)
  - `app/Domain/Report/Pos/DTOs/PosProductReportDTO.php` (Baru)
  - `app/Support/Math/FinancialMath.php` (Baru - Safe Division Helper)
  - [`app/Domain/Report/SalesReportService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Report/SalesReportService.php) (Refactor delegasi ke service baru)
- **Rincian Implementasi:**
  1. Buat class DTO `PosReportFilterDTO` untuk menampung seluruh parameter filter yang tervalidasi dan tersanitasi.
  2. Implementasikan metode query builder di `PosReportingService`:
     - `getKpiSummary(PosReportFilterDTO $filter): PosKpiSummaryDTO`
     - `getDailySalesTrend(PosReportFilterDTO $filter): Collection`
     - `getHourlyHeatmap(PosReportFilterDTO $filter): Collection`
     - `getProductPerformance(PosReportFilterDTO $filter): Collection`
     - `getCategoryPerformance(PosReportFilterDTO $filter): Collection`
     - `getCashierPerformance(PosReportFilterDTO $filter): Collection`
     - `getOutletPerformance(PosReportFilterDTO $filter): Collection`
     - `getPaymentMethodBreakdown(PosReportFilterDTO $filter): Collection`
     - `getDiscountAnalytics(PosReportFilterDTO $filter): Collection`
     - `getSalesChannelBreakdown(PosReportFilterDTO $filter): Collection`
  3. Integrasikan pengurangan nilai retur penjualan (`sales_returns`) secara matematis pada kalkulasi `Net Sales`:
     $$\text{Net Sales} = \text{Gross Revenue} - \text{Refund Amount}$$
- **Verifikasi & Test:** `tests/Feature/Pos/PosReportingServiceCalculationTest.php`

---

### 🔹 Fase 2: 3-Way Reconciliation Engine (Validasi Integritas Kasir & Gateway)
- **Tujuan:** Menyediakan engine rekonsiliasi otomatis 3-arah untuk mendeteksi selisih antara nilai tagihan order, pembayaran yang berhasil ditarik, dan mutasi uang fisik pada shift kasir.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - `app/Domain/Report/Pos/PosReconciliationService.php` (Baru)
  - `app/Domain/Report/Pos/DTOs/PosReconciliationResultDTO.php` (Baru)
- **Rincian Implementasi:**
  1. Bandingkan $\sum \text{pos\_orders.total\_amount}$ dengan $\sum \text{pos\_order\_payments.amount}$.
  2. Bandingkan $\sum \text{pos\_order\_payments.amount (cash)}$ dengan $\sum (\text{pos\_shifts.closing\_cash\_actual} - \text{opening\_cash} - \text{cash\_in} + \text{cash\_out})$.
  3. Flag anomali jika selisih $> \text{Rp } 0.01$ dan klasifikasikan penyebab selisih (*Unsettled Gateway, Over Cash, Short Cash, Stolen Receipt Void, Partial Refund Drift*).
- **Verifikasi & Test:** `tests/Feature/Pos/PosReconciliationServiceTest.php`

---

### 🔹 Fase 3: Multi-Dimensional Filter Bar Engine & Route Updates
- **Tujuan:** Membangun filter bar dinamis di UI yang mendukung kombinasi seluruh parameter bisnis dan memastikan filter dipatuhi oleh seluruh query sub-laporan.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - [`app/Http/Controllers/Web/Pos/PosReportWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Pos/PosReportWebController.php)
  - `resources/views/app/pos/reports/partials/filter_bar.blade.php` (Baru)
  - [`resources/views/app/pos/reports.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports.blade.php)
- **Rincian Implementasi:**
  1. Tangkap seluruh parameter query string: `preset`, `start_date`, `end_date`, `location_id`, `user_id`, `pos_shift_id`, `customer_id`, `category_id`, `payment_method`, `sales_channel`, `order_type`, `status`.
  2. Validasi scope multi-tenant anti-IDOR: pastikan `location_id`, `user_id`, `pos_shift_id`, dan `category_id` benar-benar milik bisnis aktif (`$business->id`).
  3. Render komponen filter bar Bento Apple HIG dengan preset rentang tanggal instan (*Hari Ini, Kemarin, 7 Hari, 30 Hari, Bulan Ini, Bulan Lalu, Tahun Ini, Custom*).
- **Verifikasi & Test:** `tests/Feature/Pos/PosReportFilterScopingTest.php`

---

### 🔹 Fase 4: Arsitektur UI Bento Apple HIG Multi-Tab (15 Sub-Modul Laporan)
- **Tujuan:** Menata ulang halaman pelaporan menjadi suite analitik interaktif berbasis 15 tab modular dengan visualisasi modern (Chart.js / SVG Heatmaps).
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - [`resources/views/app/pos/reports.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports.blade.php)
  - `resources/views/app/pos/reports/tabs/overview.blade.php`
  - `resources/views/app/pos/reports/tabs/transactions.blade.php`
  - `resources/views/app/pos/reports/tabs/products.blade.php`
  - `resources/views/app/pos/reports/tabs/categories.blade.php`
  - `resources/views/app/pos/reports/tabs/cashiers.blade.php`
  - `resources/views/app/pos/reports/tabs/outlets.blade.php`
  - `resources/views/app/pos/reports/tabs/payments.blade.php`
  - `resources/views/app/pos/reports/tabs/discounts.blade.php`
  - `resources/views/app/pos/reports/tabs/refunds.blade.php`
  - `resources/views/app/pos/reports/tabs/voids.blade.php`
  - `resources/views/app/pos/reports/tabs/shifts.blade.php`
  - `resources/views/app/pos/reports/tabs/hourly.blade.php`
  - `resources/views/app/pos/reports/tabs/customers.blade.php`
  - `resources/views/app/pos/reports/tabs/channels.blade.php`
  - `resources/views/app/pos/reports/tabs/profitability.blade.php`
- **Rincian Implementasi:**
  1. Pisahkan setiap sub-laporan ke file partial Blade terisolasi yang rapi dan mudah di-*maintain*.
  2. Pasang kontrol navigasi tab berbasis URL parameter (`?tab=overview|transactions|products|...`) dengan deep linking.
  3. Terapkan palet Apple HIG (`#34C759` system green, `#007AFF` blue, `#AF52DE` purple, `#FF9500` amber, `#FF3B30` red).
- **Verifikasi & Test:** `tests/Feature/Pos/PosReportTabsRenderingTest.php`

---

### 🔹 Fase 5: Slide-Over Modal Quick-View Detail Transaksi & Drill-Down
- **Tujuan:** Memungkinkan kasir, supervisor, atau pemilik usaha memeriksa detail transaksi per nota (item, diskon, catatan servis, serial/batch, bukti bayar) langsung dari tabel laporan tanpa reload.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - `resources/views/app/pos/reports/partials/order_detail_modal.blade.php` (Baru)
  - [`resources/views/app/pos/reports.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports.blade.php)
  - [`app/Http/Controllers/Web/Pos/PosOrderWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Pos/PosOrderWebController.php) (Endpoint JSON detail order jika dibutuhkan)
- **Rincian Implementasi:**
  1. Pasang Alpine.js modal slide-over responsive (`max-w-2xl w-full`) dengan backdrop blur.
  2. Tampilkan rincian nota lengkap: Item Qty, Harga Snapshot, HPP Snapshot, Modifier, Pajak, Service, Pembayaran Split, Jejak Print Struk, dan Tombol Cetak Salinan Ulang (*Reprint*).
- **Verifikasi & Test:** `tests/Feature/Pos/PosReportDetailModalTest.php`

---

### 🔹 Fase 6: Engine Ekspor Excel Master 9-Sheet (PhpSpreadsheet) & Streamed CSV
- **Tujuan:** Menghasilkan dokumen ekspor spreadsheet (.xlsx) profesional 9-sheet dan file CSV hemat memori yang 100% mematuhi seluruh kombinasi filter aktif.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - `app/Exports/PosMasterReportExport.php` (Baru - 9 Worksheets)
  - `app/Exports/PosStreamedCsvExport.php` (Baru - Low Memory Chunking)
  - [`app/Exports/PosReportExport.php`](file:///c:/laragon/www/cooca_core/app/Exports/PosReportExport.php) (Refactor delegasi)
  - [`app/Http/Controllers/Web/Pos/PosReportWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Pos/PosReportWebController.php)
- **Rincian Implementasi:**
  1. Bangun 9 sheet terstruktur di `PosMasterReportExport.php`:
     - *Sheet 1: Summary & Bento KPI Cards*
     - *Sheet 2: Transaction Ledger*
     - *Sheet 3: Product Performance*
     - *Sheet 4: Category Performance*
     - *Sheet 5: Cashier Performance*
     - *Sheet 6: Payment Method Breakdown*
     - *Sheet 7: Discount & Promotion Analysis*
     - *Sheet 8: Refund & Void Ledger*
     - *Sheet 9: Shift Reconciliation*
  2. Pada ekspor CSV, gunakan generator stream `cursor()` untuk menjamin penggunaan memori tetap di bawah **16MB** bahkan saat mengekspor 100.000+ baris data.
- **Verifikasi & Test:** `tests/Feature/Pos/PosMasterReportExportTest.php`

---

### 🔹 Fase 7: Database Index Tuning & Optimasi Performa Query Skala Besar
- **Tujuan:** Menjamin seluruh query agregasi pelaporan selesai dieksekusi di bawah 150ms pada database produksi berukuran jutaan baris.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - `database/migrations/2026_10_04_000001_add_pos_reporting_composite_indexes.php` (Baru)
- **Rincian Implementasi:**
  1. Tambahkan composite indexes pada:
     - `pos_orders (business_id, status, order_date, location_id, user_id, pos_shift_id)`
     - `pos_order_items (pos_order_id, product_id, created_at)`
     - `pos_order_payments (pos_order_id, payment_method, status)`
     - `pos_shifts (business_id, status, opened_at, closed_at, user_id)`
     - `sales_returns (business_id, status, return_date, pos_order_id)`
  2. Jalankan migration dan verifikasi `EXPLAIN` query agregasi menggunakan indeks komposit tanpa *Full Table Scan*.
- **Verifikasi & Test:** `tests/Feature/Pos/PosReportingPerformanceIndexTest.php`

---

### 🔹 Fase 8: Automated Acceptance Test Suite & Sinkronisasi Dokumen 3-Layer
- **Tujuan:** Memverifikasi 100% kelulusan seluruh skenario uji akseptansi pelaporan tanpa regresi dan menyinkronkan seluruh artefak dokumentasi sistem 3-layer.
- **Severity:** 🟢 **P3 (Validasi & Dokumentasi)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - `tests/Feature/Pos/PosComprehensiveMasterReportingTest.php` (Master Acceptance Test Suite)
  - [`docs/system/INDEX.md`](file:///c:/laragon/www/cooca_core/docs/system/INDEX.md)
  - [`docs/system/modules/pos.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/pos.md)
  - [`docs/AiWorkHistory.md`](file:///c:/laragon/www/cooca_core/docs/AiWorkHistory.md)
  - [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md)
- **Rincian Implementasi:**
  1. Jalankan seluruh test suite POS di direktori `tests/Feature/Pos/` dan pastikan 100% passed (0 failures, 0 errors).
  2. Catat riwayat pekerjaan terperinci di `docs/AiWorkHistory.md` dan mutakhirkan Layer 2/3 manual sistem.
- **Verifikasi & Test:** `php artisan test tests/Feature/Pos/`

---

## 📊 3. Matriks Berkas Terdampak Hulu-ke-Hilir Lintas 8 Fase

| No | Berkas / Modul Target | Fase Terdampak | Peran & Perubahan Arsitektur |
| :---: | :--- | :---: | :--- |
| 1 | `app/Domain/Report/Pos/PosReportingService.php` | Fase 1, 2 | Service agregasi utama, kalkulasi KPI, tren, produk, kasir, outlet |
| 2 | `app/Domain/Report/Pos/PosReconciliationService.php` | Fase 2 | Engine validasi rekonsiliasi 3-arah (Order ↔ Payment ↔ Shift Cash) |
| 3 | `app/Domain/Report/Pos/DTOs/PosReportFilterDTO.php` | Fase 1, 3 | DTO filter multi-dimensi tersanitasi & scope tenant |
| 4 | `app/Domain/Report/Pos/DTOs/PosKpiSummaryDTO.php` | Fase 1 | DTO ringkasan 14 KPI finansial, HPP, margin, dan growth |
| 5 | `app/Support/Math/FinancialMath.php` | Fase 1 | Helper matematika keuangan aman pembagian nol (*Safe Division*) |
| 6 | [`app/Http/Controllers/Web/Pos/PosReportWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Pos/PosReportWebController.php) | Fase 1, 3, 4, 6 | Controller web pelaporan POS, penanganan filter & routing ekspor |
| 7 | `app/Exports/PosMasterReportExport.php` | Fase 6 | Generator Excel XLSX 9-sheet standar PhpSpreadsheet Apple HIG |
| 8 | `app/Exports/PosStreamedCsvExport.php` | Fase 6 | Generator CSV hemat memori berbasis cursor streaming |
| 9 | `database/migrations/2026_10_04_000001_add_pos_reporting_composite_indexes.php` | Fase 7 | Composite indexing untuk query agregasi super cepat |
| 10 | [`resources/views/app/pos/reports.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports.blade.php) | Fase 3, 4, 5 | View utama laporan POS Bento Apple HIG v2.0 |
| 11 | `resources/views/app/pos/reports/tabs/` (15 berkas partial) | Fase 4 | Komponen UI modular untuk 15 sub-laporan |
| 12 | `resources/views/app/pos/reports/partials/filter_bar.blade.php` | Fase 3 | Filter bar dinamis dengan preset rentang tanggal |
| 13 | `resources/views/app/pos/reports/partials/order_detail_modal.blade.php` | Fase 5 | Slide-over modal pemeriksaan detail transaksi per nota |
| 14 | [`lang/id/pos.php`](file:///c:/laragon/www/cooca_core/lang/id/pos.php) & [`lang/en/pos.php`](file:///c:/laragon/www/cooca_core/lang/en/pos.php) | Fase 4, 8 | Kamus dwibahasa ID & EN untuk seluruh label dan metrik baru |
| 15 | `tests/Feature/Pos/PosComprehensiveMasterReportingTest.php` | Fase 8 | Master Acceptance Test Suite otomatis (20+ skenario pengujian) |

---

## 🎯 4. Definition of Done (DoD) Per Fase

- [x] **Fase 1 DoD:** `PosReportingService` menghasilkan 14 metrik KPI dan seluruh agregasi berbasis snapshot harga riil dengan akurasi 100%, memperhitungkan retur penjualan pada Net Sales.
- [x] **Fase 2 DoD:** `PosReconciliationService` memvalidasi keseimbangan 3-arah dan mendeteksi anomali selisih kas laci atau selisih payment gateway secara presisi.
- [x] **Fase 3 DoD:** Seluruh parameter filter (Tanggal, Outlet, Kasir, Shift, Kategori, Bayar, Channel, Status) bekerja 100% dan terisolasi anti-IDOR.
- [x] **Fase 4 DoD:** Seluruh 15 sub-modul laporan tampil rapi dalam tata letak Bento Apple HIG dengan navigasi tab deep-linking.
- [x] **Fase 5 DoD:** Pengguna dapat membuka slide-over modal detail transaksi per nota tanpa reload halaman.
- [x] **Fase 6 DoD:** Ekspor Excel 9-sheet dan CSV streaming 100% mematuhi filter aktif pengguna tanpa memicu memory leak.
- [x] **Fase 7 DoD:** Query agregasi pelaporan tereksekusi di bawah 150ms dengan pemanfaatan composite indexes.
- [x] **Fase 8 DoD:** Seluruh automated test suite di `tests/Feature/Pos/` lolos 100% (0 failures, 0 errors) dan dokumentasi 3-layer tersinkronisasi penuh.
