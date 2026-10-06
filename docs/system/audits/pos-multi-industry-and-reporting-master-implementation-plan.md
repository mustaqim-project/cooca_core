# Master Implementation Plan: Rekayasa POS & Reporting Lintas 20 Industri & Online Order Delivery F&B (Fase 1 – 7)

**Dokumen Standar Layer 2:** [`docs/system/audits/pos-multi-industry-and-reporting-master-implementation-plan.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/pos-multi-industry-and-reporting-master-implementation-plan.md)  
**Dokumen Rujukan Audit:** [`docs/system/audits/pos-multi-industry-and-reporting-comprehensive-audit.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/pos-multi-industry-and-reporting-comprehensive-audit.md)  
**Status:** `READY FOR SYSTEMATIC EXECUTION (Road to Production - 7 Phased Roadmap)`  
**Target Sistem:** Multi-Industry POS Terminal, POS Reporting & Analytics Suite, Online Food Delivery Financial Engine (ShopeeFood, GoFood, GrabFood), Multi-Channel Commission & Net Payout Ledger, Bento Apple HIG 15-Tab UI, Slide-Over Modal, Master 9-Sheet Excel Export, dan Automated Acceptance Tests.

---

## 📋 1. Ringkasan Eksekutif Rencana Kerja

Rencana implementasi master ini dirancang secara terstruktur dan berorientasi produksi (*Road to Production*) untuk mengeksekusi rekomendasi perbaikan dari laporan audit komprehensif. Fokus utama adalah menghadirkan **mesin kalkulasi finansial saluran online order (ShopeeFood, GoFood, GrabFood)** yang akurat, penegakan **Dynamic Context-Aware Auto-Hiding pada 20 industri**, visualisasi UI Bento Apple HIG modern, serta pengujian otomatis 100% tanpa regresi.

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│             ROADMAP MASTER IMPLEMENTASI POS & REPORTING LINTAS 20 INDUSTRI (7 FASE)              │
├──────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Fase 1: Engine Finansial Online Food Delivery F&B (ShopeeFood, GoFood, Grab MDR & Net Payout)   │
│ Fase 2: Upgrade UI Bento Apple HIG Tab Saluran Penjualan (tabs/channels.blade.php)              │
│ Fase 3: Integrasi Badge Saluran & Metadata 20 Industri pada Ledger Transaksi (transactions)     │
│ Fase 4: Slide-Over Modal Quick-View Detail Transaksi Sadar Konteks 20 Industri & Ojol           │
│ Fase 5: Upgrade Master Excel 9-Sheet Exporter (PhpSpreadsheet Rincian Ojol & 20 Industri)       │
│ Fase 6: Automated Acceptance Test Suite (20 Industri & Ojol Channels) & Production Hardening   │
│ Fase 7: Sinkronisasi Dokumentasi Sistem 3-Layer & Production Sign-Off                           │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ 2. Rincian Teknis Eksekusi Per Fase (Fase 1 – 7)

---

### 🔹 Fase 1: Engine Finansial Online Food Delivery F&B (MDR Komisi & Net Payout)
- **Tujuan Bisnis:** Membedakan antara Omzet Kotor (Gross GMV), Diskon Promo Resto, Biaya Komisi Platform Mitra (20%), dan Net Payout Bersih yang cair ke rekening resto, sehingga pemilik kuliner mengetahui profitabilitas riil tiap saluran ojol.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - [`app/Domain/Report/Pos/PosReportingService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Report/Pos/PosReportingService.php)
  - [`app/Domain/Report/Pos/DTOs/PosReportFilterDTO.php`](file:///c:/laragon/www/cooca_core/app/Domain/Report/Pos/DTOs/PosReportFilterDTO.php)
- **Rincian Implementasi:**
  1. Pada metode `getSalesChannelBreakdown(PosReportFilterDTO $filter)`, tambahkan kalkulasi terstruktur:
     - `gross_sales`: Total omzet kotor sebelum potongan platform.
     - `store_discount`: Potongan diskon/voucher yang ditanggung merchant.
     - `net_sales`: Nilai penjualan makanan murni (`gross_sales - store_discount`).
     - `platform_fee_percent`: Default 20.0% untuk `shopeefood` dan `gofood`, 25.0% untuk `grabfood`, 0% untuk `pos_direct` / `storefront`.
     - `platform_fee_amount`: `net_sales * (platform_fee_percent / 100)`.
     - `net_merchant_payout`: Hak bersih yang diterima resto (`net_sales - platform_fee_amount`).
     - `total_cogs_hpp`: Total HPP bahan baku (BOM cost).
     - `gross_profit`: Laba kotor riil merchant (`net_merchant_payout - total_cogs_hpp`).
     - `real_margin_percent`: Persentase laba kotor terhadap net payout (`gross_profit / net_merchant_payout * 100`).
  2. Pastikan seluruh perhitungan menggunakan helper [`FinancialMath::safeDivide()`](file:///c:/laragon/www/cooca_core/app/Support/Math/FinancialMath.php) untuk mencegah division by zero.
- **Verifikasi & Test:** Unit test perhitungan di `PosReportingServiceCalculationTest.php`.

---

### 🔹 Fase 2: Upgrade UI Bento Apple HIG Tab Saluran Penjualan (`tabs/channels.blade.php`)
- **Tujuan Bisnis:** Menyajikan dashboard visual perbandingan saluran penjualan kasir vs toko online vs aplikasi ojol dengan palet warna resmi dan kartu metrik eksekutif.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - [`resources/views/app/pos/reports/tabs/channels.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports/tabs/channels.blade.php)
  - [`lang/id/pos.php`](file:///c:/laragon/www/cooca_core/lang/id/pos.php) & [`lang/en/pos.php`](file:///c:/laragon/www/cooca_core/lang/en/pos.php)
- **Rincian Implementasi:**
  1. Pasang 4 Kartu Bento KPI di bagian atas:
     - **Total Omzet Saluran (GMV):** Akumulasi seluruh pesanan online & offline.
     - **Estimasi Komisi Platform Ojol:** Total fee komisi yang dipotong ShopeeFood/GoFood/GrabFood.
     - **Total Net Payout Hak Resto:** Estimasi dana bersih yang cair ke rekening merchant.
     - **Rata-rata Margin Bersih Ojol:** Margin kotor riil setelah dipotong komisi dan HPP.
  2. Lengkapi tabel saluran dengan kolom: *Saluran Jual, Jumlah Order, Omzet Bruto (GMV), Komisi Platform (20%), Net Payout Hak Resto, Total HPP, Laba Kotor, Margin Riil %*.
  3. Sematkan badge warna resmi:
     - `shopeefood` ➔ Oranye Shopee (`#EE4D2D`)
     - `gofood` ➔ Merah GoBiz (`#EE2724`)
     - `grabfood` ➔ Hijau Grab (`#00B14F`)
     - `storefront` ➔ Biru Toko Online (`#007AFF`)
     - `pos_direct` ➔ Ungu Kasir Resto (`#AF52DE`)
- **Verifikasi & Test:** Rendering test di `PosReportTabsRenderingTest.php`.

---

### 🔹 Fase 3: Integrasi Badge Saluran & Metadata 20 Industri pada Ledger Transaksi
- **Tujuan Bisnis:** Memudahkan kasir dan owner mengenali nomor pesanan ojol eksternal (`SF-9021`, `GF-4312`) dan data kontekstual industri (Nopol Bengkel, Rak Laundry, No Resep Apotek) langsung dari daftar transaksi.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - [`resources/views/app/pos/reports/tabs/transactions.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports/tabs/transactions.blade.php)
  - [`app/Domain/Report/Pos/PosReportingService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Report/Pos/PosReportingService.php) (Eager loading relasi transaksi)
- **Rincian Implementasi:**
  1. Pada kolom nomor order/referensi, tampilkan badge adaptif:
     - **F&B Ojol:** Badge Saluran + Nomor Order Eksternal (`external_order_number` / `table_or_reference`).
     - **Bengkel Otomotif (`service_workshop`):** Badge Nopol Kendaraan (`vehicle_plate`) & KM Servis.
     - **Laundry Kiloan (`service_laundry`):** Badge Berat KG & Nomor Rak Cucian.
     - **Apotek (`retail_pharmacy`):** Badge Nomor Resep Dokter.
     - **Elektronik (`retail_electronics`):** Badge Nomor Serial / IMEI.
  2. Terapkan conditional Blade `@if($business->isModuleEnabled(...))` agar kolom yang tidak relevan dengan industri aktif otomatis tersembunyi (*Auto-Hiding*).
- **Verifikasi & Test:** Acceptance test di `PosReportTabsRenderingTest.php`.

---

### 🔹 Fase 4: Slide-Over Modal Quick-View Detail Transaksi Sadar Konteks 20 Industri & Ojol
- **Tujuan Bisnis:** Menampilkan rincian komisi platform ojol dan metadata industri spesifik di dalam laci modal detail nota tanpa reload halaman.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - [`app/Http/Controllers/Web/Pos/PosReportWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Pos/PosReportWebController.php)
  - [`resources/views/app/pos/reports/partials/order_detail_modal.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports/partials/order_detail_modal.blade.php)
- **Rincian Implementasi:**
  1. Pada respon JSON method `orderDetail()` di `PosReportWebController`, tambahkan data:
     - `channel_meta`: `{ channel_name, external_order_number, platform_fee_percent, platform_fee_amount, net_payout }`.
     - `industry_meta`: `{ vehicle_plate, odometer, laundry_weight, rack_number, prescription_number, serial_numbers }`.
  2. Pada template Blade modal Alpine.js, buat section Bento Card khusus "Rincian Saluran Ojol & Payout" dan "Informasi Kontekstual Industri".
- **Verifikasi & Test:** JSON test di `PosReportOrderDetailModalTest.php`.

---

### 🔹 Fase 5: Upgrade Master Excel 9-Sheet Exporter (PhpSpreadsheet Rincian Ojol & 20 Industri)
- **Tujuan Bisnis:** Memastikan dokumen spreadsheet `.xlsx` master memuat informasi saluran penjualan ojol dan metadata industri secara transparan untuk rekonsiliasi pembukuan.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - [`app/Exports/PosReportExport.php`](file:///c:/laragon/www/cooca_core/app/Exports/PosReportExport.php)
- **Rincian Implementasi:**
  1. **Sheet 1 (Ringkasan Eksekutif):** Tambahkan tabel agregasi "Performa Saluran Penjualan & Ojol Delivery" (Gross GMV, Komisi Platform, Net Payout, Laba Kotor).
  2. **Sheet 2 (Buku Transaksi):** Tambahkan kolom *Saluran Jual, No. Order Ojol/Ref, Komisi Platform (Rp), Net Payout (Rp)*.
  3. **Sheet 7 (Metode Pembayaran):** Rinci pembayaran agregator Ojol (ShopeePay Merchant, GoBiz Payout, GrabMerchant).
- **Verifikasi & Test:** Export test di `PosReportExcelExportTest.php`.

---

### 🔹 Fase 6: Automated Acceptance Test Suite (20 Industri & Ojol Channels) & Production Hardening
- **Tujuan Bisnis:** Membuktikan 100% kebenaran matematis, keamanan multi-tenant, dan rendering antarmuka melalui pengujian otomatis tanpa regresi sebelum rilis ke produksi.
- **Severity:** 🟢 **P3 (QA & Testing)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - `tests/Feature/Pos/PosMultiIndustryAndOnlineOrderReportingTest.php` (Baru)
- **Rincian Implementasi:**
  1. Uji skenario transaksi F&B ShopeeFood, GoFood, GrabFood dengan markup harga dan potongan komisi 20%.
  2. Uji skenario multi-industri: Bengkel (Nopol/Mekanik), Laundry (Berat/Rak), Apotek (Batch/Resep).
  3. Uji integritas ekspor Excel 9-sheet dan endpoint JSON modal.
  4. Eksekusi seluruh test suite POS (`vendor/bin/phpunit tests/Feature/Pos/`) hingga 100% passed.
- **Verifikasi & Test:** PHPUnit Feature Tests.

---

### 🔹 Fase 7: Sinkronisasi Dokumentasi Sistem 3-Layer & Production Sign-Off
- **Tujuan Bisnis:** Memastikan seluruh artefak dokumentasi resmi COOCA terbarui dan mencerminkan kapabilitas pelaporan 20 industri dan online food delivery secara akurat.
- **Severity:** 🟢 **P3 (Dokumentasi)**
- **Berkas yang Dibuat / Dimodifikasi:**
  - [`docs/AiWorkHistory.md`](file:///c:/laragon/www/cooca_core/docs/AiWorkHistory.md)
  - [`docs/system/modules/pos.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/pos.md)
  - [`docs/system/INDEX.md`](file:///c:/laragon/www/cooca_core/docs/system/INDEX.md)
  - [`docs/system/audits/pos-multi-industry-and-reporting-master-implementation-plan.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/pos-multi-industry-and-reporting-master-implementation-plan.md)
- **Rincian Implementasi:**
  1. Catat entri `[WORK-2026-10-04-294]` di `docs/AiWorkHistory.md`.
  2. Perbarui Layer 2 manual sistem di `docs/system/modules/pos.md`.
  3. Tandai seluruh checklist Definition of Done sebagai `[x] COMPLETED`.

---

## 📊 3. Matriks Berkas Terdampak Hulu-ke-Hilir Lintas 7 Fase

| No | Berkas Target | Fase | Peran & Perubahan Teknis |
| :---: | :--- | :---: | :--- |
| 1 | [`app/Domain/Report/Pos/PosReportingService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Report/Pos/PosReportingService.php) | Fase 1, 3 | Kalkulasi komisi platform ojol 20%, Net Payout resto, dan eager loading metadata 20 industri. |
| 2 | [`app/Http/Controllers/Web/Pos/PosReportWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Pos/PosReportWebController.php) | Fase 4 | Endpoint modal JSON detail order dengan payload rincian komisi ojol & data kontekstual 20 industri. |
| 3 | [`resources/views/app/pos/reports/tabs/channels.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports/tabs/channels.blade.php) | Fase 2 | Bento KPI Cards Ojol, tabel breakdown komisi platform, badge warna Shopee/GoFood/Grab. |
| 4 | [`resources/views/app/pos/reports/tabs/transactions.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports/tabs/transactions.blade.php) | Fase 3 | Badge saluran ojol (No Order SF/GF), Nopol bengkel, berat laundry dengan auto-hiding. |
| 5 | [`resources/views/app/pos/reports/partials/order_detail_modal.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports/partials/order_detail_modal.blade.php) | Fase 4 | Drawer slide-over dengan kartu rincian payout ojol dan metadata industri aktif. |
| 6 | [`app/Exports/PosReportExport.php`](file:///c:/laragon/www/cooca_core/app/Exports/PosReportExport.php) | Fase 5 | Master 9-sheet Excel exporter dengan integrasi breakdown saluran ojol & metadata industri. |
| 7 | [`lang/id/pos.php`](file:///c:/laragon/www/cooca_core/lang/id/pos.php) & [`lang/en/pos.php`](file:///c:/laragon/www/cooca_core/lang/en/pos.php) | Fase 2, 4 | Kamus dwibahasa untuk label komisi ojol, net payout, dan istilah 20 industri. |
| 8 | `tests/Feature/Pos/PosMultiIndustryAndOnlineOrderReportingTest.php` | Fase 6 | Master Acceptance Test Suite 20 industri & kanal ojol. |
| 9 | [`docs/system/audits/pos-multi-industry-and-reporting-master-implementation-plan.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/pos-multi-industry-and-reporting-master-implementation-plan.md) | Fase 7 | Dokumen rencana master Layer 2. |

---

## 🎯 4. Definition of Done (DoD) Per Fase

- [ ] **Fase 1 DoD:** `PosReportingService` menghasilkan metrik komisi platform ojol (20%), Net Payout merchant, dan laba kotor riil ojol dengan akurasi 100% tanpa division by zero.
- [ ] **Fase 2 DoD:** Tab Saluran Penjualan (`channels.blade.php`) menampilkan 4 Bento KPI Cards Ojol, tabel komisi, dan badge warna resmi ShopeeFood/GoFood/GrabFood/Storefront.
- [ ] **Fase 3 DoD:** Ledger Transaksi (`transactions.blade.php`) menampilkan badge nomor pesanan ojol dan metadata 20 industri secara adaptif (*Auto-Hiding*).
- [ ] **Fase 4 DoD:** Modal Quick-View Detail Transaksi merinci komisi ojol, net payout, dan data kontekstual industri via JSON endpoint yang aman dari IDOR.
- [ ] **Fase 5 DoD:** Seluruh 9 sheet Excel memuat rincian saluran penjualan ojol dan metadata transaksi secara presisi dengan formula =SUM().
- [ ] **Fase 6 DoD:** Automated Test Suite `PosMultiIndustryAndOnlineOrderReportingTest.php` dan seluruh test suite POS di `tests/Feature/Pos/` lolos 100% (0 errors, 0 failures).
- [ ] **Fase 7 DoD:** Riwayat pekerjaan tercatat di `docs/AiWorkHistory.md`, Layer 2 manual di `docs/system/modules/pos.md` tersinkronisasi, dan seluruh checklist ditandai `[x] COMPLETED`.
