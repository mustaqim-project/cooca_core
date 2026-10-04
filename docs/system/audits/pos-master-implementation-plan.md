# Master Implementation Plan: Remediasi Ekosistem POS & Kasir Multi-Tenant COOCA (Fase 1 – 10)

**Dokumen Standar Layer 2:** [`docs/system/audits/pos-master-implementation-plan.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/pos-master-implementation-plan.md)  
**Rujukan Laporan Audit:** [`docs/system/audits/pos-comprehensive-audit.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/pos-comprehensive-audit.md)  
**Status:** `READY FOR EXECUTION & VERIFICATION (10 Phased Roadmap)`

---

## 📋 1. Ringkasan Eksekutif Rencana Kerja

Rencana implementasi master ini dirancang secara sistematis untuk mengeksekusi perbaikan dan hardening terhadap **22 Temuan Faktual (`F-01` s/d `F-22`)** pada seluruh modul Kasir & POS ([`resources/views/app/pos/`](file:///c:/laragon/www/cooca_core/resources/views/app/pos)) dan rantai 11 simpul eksekusi backend secara bertahap, terisolasi, dan terverifikasi tanpa regresi.

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│                   ROADMAP MASTER IMPLEMENTASI POS COOCA (10 FASE TERPADU)                        │
├──────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Fase 1: Perbaikan Kritis Integritas Shift & Reversal Finansial (F-01, F-02, F-04)                │
│ Fase 2: Standardisasi FormRequest & Penanganan Error Respon JSON (F-08, F-11)                    │
│ Fase 3: Dynamic Context-Aware Auto-Hiding 20 Sektor Industri (F-05, F-19)                        │
│ Fase 4: Ekstraksi Kamus Terjemahan Modular Penuh (lang/id/pos.php & lang/en/pos.php)             │
│ Fase 5: Refactor Lokalisasi 10 Berkas Blade & Injeksi window.COOCA_I18N (F-03, F-13, F-20)       │
│ Fase 6: Unifikasi 3-Baris Page Header & Modul Tabs POS (F-06, F-07, F-16)                        │
│ Fase 7: Penataan Ergonomi Mobile-First, Thumb Zone & Safari iOS Auto-Zoom (F-09, F-15)           │
│ Fase 8: Hardening Bento Apple HIG Palette, Status Online & Optimasi Smart Polling (F-10, F-14, F-22) │
│ Fase 9: Penajaman Ergonomi Fraud Prevention, Split Clamping & No-Panic Microcopy (F-12, F-17, F-21)│
│ Fase 10: Pengujian Akseptansi Otomatis (Acceptance Test Suite) & Sinkronisasi Dokumen 3-Layer    │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ 2. Rincian Teknis Eksekusi Per Fase (Fase 1 – 10)

---

### 🔹 Fase 1: Perbaikan Kritis Integritas Shift & Reversal Finansial (`F-01`, `F-02`, `F-04`)
- **Tujuan Bisnis:** Menjamin akurasi rekonsiliasi kas kasir tanpa selisih fiktif (*Phantom Cash Deficit*) dan menjamin integritas pembukuan jurnal akuntansi serta kas keluar saat terjadi void atau refund.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas Terdampak:**
  - [`app/Domain/Pos/PosShiftService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Pos/PosShiftService.php)
  - [`app/Domain/Pos/PosOrderService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Pos/PosOrderService.php)
  - [`app/Domain/Accounting/AutoJournalService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Accounting/AutoJournalService.php)
  - [`resources/views/app/pos/tables.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/tables.blade.php)
- **Rincian Implementasi:**
  1. **Kalkulasi Kas Bersih Shift (`F-01`):** Pada `PosShiftService::getShiftSummary()`, ubah perhitungan `cashSales` agar mengurangi nilai kembalian tunai pelanggan (`change_amount`) dari uang tunai bruto yang diserahkan (`$payment->amount`).
     ```php
     $netCash = max(0.0, (float)$payment->amount - (float)($order->change_amount ?? 0));
     $cashSales += $netCash;
     ```
  2. **Pembalikan Kas Keluar & Jurnal Akuntansi (`F-02`):** Pada `PosOrderService::voidOrder()` dan `refundOrder()`, panggil `CashLedgerService::recordOutflow()` dan `AutoJournalService::recordPosVoidJournal()` / `recordPosOrderRefundJournal()` di dalam blok `DB::transaction()`.
  3. **Perbaikan Error Handler Meja (`F-04`):** Pada `tables.blade.php`, hapus modifikasi optimistik di blok `catch (e)` pada fungsi `deleteTable()`; hanya tandai meja terhapus jika HTTP status `200` dan respon `{ success: true }`.
- **Verifikasi & Test:** `tests/Feature/Pos/PosAuditPhase1And2RemediationTest.php`

---

### 🔹 Fase 2: Standardisasi FormRequest & Penanganan Error Respon JSON (`F-08`, `F-11`)
- **Tujuan Bisnis:** Memastikan seluruh validasi transaksi kasir terisolasi rapi, mudah diuji, dan mengembalikan HTTP 422 JSON yang konsisten ke modal AJAX tanpa redirect HTML 302 yang memutus interaksi kasir.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas Terdampak:**
  - `app/Http/Requests/Pos/PosCheckoutRequest.php`
  - `app/Http/Requests/Pos/PosOpenShiftRequest.php`
  - `app/Http/Requests/Pos/PosCloseShiftRequest.php`
  - `app/Http/Requests/Pos/PosVoidOrderRequest.php`
  - `app/Http/Requests/Pos/PosRefundOrderRequest.php`
  - [`app/Http/Controllers/Web/Pos/PosOrderWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Pos/PosOrderWebController.php)
  - [`app/Http/Controllers/Web/Pos/PosTerminalWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Pos/PosTerminalWebController.php)
- **Rincian Implementasi:**
  1. Buat class FormRequest terdedikasi di namespace `App\Http\Requests\Pos\` dengan aturan validasi ketat untuk `items`, `payments`, `discount_value`, `customer_id`, dan atribut layanan industri.
  2. Refactor method `void()` dan `refund()` di `PosOrderWebController` agar mengembalikan `response()->json(['success' => false, 'message' => $e->getMessage()], 422)` jika terjadi exception pada panggilan AJAX modal kasir.
- **Verifikasi & Test:** `tests/Feature/Pos/PosAuditPhase1And2RemediationTest.php`

---

### 🔹 Fase 3: Dynamic Context-Aware Auto-Hiding 20 Sektor Industri (`F-05`, `F-19`)
- **Tujuan Bisnis:** Mencegah kebingungan kasir UMKM dengan menyembunyikan secara dinamis seluruh field, tab, tombol, dan modal yang tidak relevan dengan sektor bisnis aktif tenant.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas Terdampak:**
  - [`app/Models/Business.php`](file:///c:/laragon/www/cooca_core/app/Models/Business.php)
  - [`resources/views/app/pos/terminal.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/terminal.blade.php)
- **Rincian Implementasi:**
  1. Pastikan helper model `$business` lengkap: `isWorkshop()`, `isLaundry()`, `isPharmacy()`, `isFoodIndustry()`, `isRetail()`.
  2. Bungkus tab layanan vertikal SPK Bengkel dan Laundry Kiloan dengan `@if($isWorkshop)` dan `@if($isLaundry)`.
  3. Bungkus kontainer *"Atribut Khusus Apotek / Farmasi"* (Batch, Tanggal Expired, Aturan Dosis) pada modal detail item ([`L3534-L3555`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/terminal.blade.php#L3534-L3555)) dengan `@if($business->isPharmacy() || $business->isModuleEnabled('industry_pharmacy'))`.
- **Verifikasi & Test:** `tests/Feature/Pos/PosAuditPhase3And4RemediationTest.php`

---

### 🔹 Fase 4: Ekstraksi Kamus Terjemahan Modular Penuh (`lang/id/pos.php` & `lang/en/pos.php`)
- **Tujuan Bisnis:** Menyediakan fondasi lokalisasi dwibahasa penuh (Bahasa Indonesia `id` & English `en`) dengan 100% keselarasan kunci terjemahan di seluruh 11 domain POS.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas Terdampak:**
  - [`lang/id/pos.php`](file:///c:/laragon/www/cooca_core/lang/id/pos.php)
  - [`lang/en/pos.php`](file:///c:/laragon/www/cooca_core/lang/en/pos.php)
- **Rincian Implementasi:**
  1. Ekstraksi 160+ pasangan kunci terjemahan yang mencakup: Navigation Headers, Cart & Terminal, Payment & Split Modal, Shift & Blind Cash Count, Tables & QR Dine-in, Kitchen KDS, Industry Verticals, Printers & Hardware, Prep Sheet, Supervisor PIN & Anti-Fraud, Alerts & JSON Response.
  2. Tambahkan kunci baru: `quick_customer_title`, `quick_customer_desc`, `customer_name_placeholder`, `item_notes_label`, `item_notes_placeholder`, `max_split_rows_reached`, `status_online`, `status_offline`.
- **Verifikasi & Test:** `tests/Feature/Pos/PosAuditPhase3And4RemediationTest.php`

---

### 🔹 Fase 5: Refactor Lokalisasi 10 Berkas Blade & Injeksi `window.COOCA_I18N` (`F-03`, `F-13`, `F-20`)
- **Tujuan Bisnis:** Mengeliminasi seluruh teks hardcoded mentah pada 10 berkas view Blade POS dan menyuntikkan dictionary JavaScript global agar rendering modal Alpine.js instan dan responsif terhadap bahasa pilihan.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas Terdampak:**
  - [`resources/views/app/pos/terminal.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/terminal.blade.php)
  - [`resources/views/app/pos/orders.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/orders.blade.php)
  - [`resources/views/app/pos/shifts.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/shifts.blade.php)
  - [`resources/views/app/pos/kitchen.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/kitchen.blade.php)
  - [`resources/views/app/pos/tables.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/tables.blade.php)
  - [`resources/views/app/pos/reports.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports.blade.php)
  - [`resources/views/app/pos/printers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/printers/index.blade.php)
  - [`resources/views/app/pos/receipt.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/receipt.blade.php)
  - [`resources/views/app/pos/prep_sheet.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/prep_sheet.blade.php)
  - [`resources/views/app/pos/qr-card.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/qr-card.blade.php)
- **Rincian Implementasi:**
  1. Ganti seluruh string statis dengan `{{ __('pos.key') }}` di seluruh 10 view Blade.
  2. Pasang tag root dinamis `<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">` pada view mandiri.
  3. Ganti form modal Quick Add Customer ([`L2045-L2079`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/terminal.blade.php#L2045-L2079)) dengan `{{ __('pos.quick_customer_*') }}` dan helper terjemahan Alpine.js.
  4. Injeksi `window.COOCA_I18N = @json(__('pos'));` pada bagian head `terminal.blade.php`.
- **Verifikasi & Test:** `tests/Feature/Pos/PosAuditPhase5BladeLocalizationTest.php`

---

### 🔹 Fase 6: Unifikasi 3-Baris Page Header & Modul Tabs POS (`F-06`, `F-07`, `F-16`)
- **Tujuan Bisnis:** Menjamin keseragaman hierarki navigasi, title header 3-baris Apple HIG, dan kelengkapan tab antar-submodul POS tanpa duplikasi visual.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas Terdampak:**
  - [`resources/views/app/pos/printers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/printers/index.blade.php)
  - [`resources/views/app/pos/prep_sheet.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/prep_sheet.blade.php)
  - [`resources/views/app/pos/shifts.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/shifts.blade.php)
  - [`app/Support/Navigation/NavigationRegistry.php`](file:///c:/laragon/www/cooca_core/app/Support/Navigation/NavigationRegistry.php)
- **Rincian Implementasi:**
  1. Ganti header custom mentah pada `printers/index.blade.php` dan `prep_sheet.blade.php` dengan `<x-module-header module="pos" ...>` dan sertakan `<x-module-tabs module="pos" />`.
  2. Hapus parameter duplikat `headerTitle` dan `headerSubtitle` dari `@extends('layouts.app')` pada `shifts.blade.php`.
  3. Daftarkan tab `printers` ('Printer & Hardware') di bawah modul `pos` pada `NavigationRegistry.php` dengan rute aktif `['pos.printers.*', 'settings.pos.printers.*']`.
- **Verifikasi & Test:** `tests/Feature/Pos/PosAuditPhase6HeaderAndTabsTest.php`

---

### 🔹 Fase 7: Penataan Ergonomi Mobile-First, Thumb Zone & Safari iOS Auto-Zoom (`F-09`, `F-15`)
- **Tujuan Bisnis:** Mengoptimalkan kenyamanan operasional satu tangan (Thumb Zone) pada layar ponsel (360px–390px), memperbesar target sentuh minimal 48px, dan mencegah auto-zoom paksa Safari iOS.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas Terdampak:**
  - [`resources/views/app/pos/terminal.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/terminal.blade.php)
  - [`resources/views/app/pos/orders.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/orders.blade.php)
  - [`resources/views/app/pos/kitchen.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/kitchen.blade.php)
- **Rincian Implementasi:**
  1. Tambahkan aturan CSS media query anti-zoom Safari iOS `@media screen and (max-width: 768px) { input:not([type="checkbox"]):not([type="radio"]), select, textarea { font-size: 16px !important; } }`.
  2. Sesuaikan ukuran modal panel `.pos-modal-panel` dengan `max-width: min(calc(100vw - 1rem), 42rem)`.
  3. Tingkatkan ukuran target sentuh tombol aksi kasir dan kartu pesanan dapur menjadi minimal 48x48px (`min-h-[48px] sm:min-h-0 sm:h-9` dan `min-h-[48px] h-12`).
- **Verifikasi & Test:** `tests/Feature/Pos/PosAuditPhase7ResponsiveErgonomicsTest.php`

---

### 🔹 Fase 8: Hardening Bento Apple HIG Palette, Status Online & Optimasi Smart Polling (`F-10`, `F-14`, `F-22`)
- **Tujuan Bisnis:** Menstandarisasi palet warna visual Bento Apple HIG (`#34C759` green, `#007AFF` blue), menampilkan indikator konektivitas internet, dan menghemat baterai/kuota tablet kasir via Smart Polling `document.hidden`.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas Terdampak:**
  - [`resources/views/app/pos/prep_sheet.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/prep_sheet.blade.php)
  - [`resources/views/app/pos/terminal.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/terminal.blade.php)
  - [`resources/views/app/pos/kitchen.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/kitchen.blade.php)
- **Rincian Implementasi:**
  1. Konversi seluruh class `emerald-*` pada `prep_sheet.blade.php` ke palet sistem Apple HIG (`#34C759` system green, `#007AFF` blue, `#AF52DE` purple, `#FF3B30` red).
  2. Tambahkan listener event konektivitas `online` dan `offline` pada `terminal.blade.php` serta render badge indikator koneksi di topbar.
  3. Terapkan *Page Visibility Aware Smart Polling* (`document.hidden` check & `visibilitychange` event listener) pada fungsi polling pesanan meja QR `terminal.blade.php` dan polling KDS `kitchen.blade.php`.
- **Verifikasi & Test:** `tests/Feature/Pos/PosAuditPhase8HigPaletteAndPollingTest.php`

---

### 🔹 Fase 9: Penajaman Ergonomi Fraud Prevention, Split Clamping & No-Panic Microcopy (`F-12`, `F-17`, `F-21`)
- **Tujuan Bisnis:** Mencegah mutasi ganda akibat keyboard Enter spamming, menerapkan sanitasi auto-clamping pada pembayaran split, dan meredakan kepanikan kasir saat otorisasi PIN supervisor.
- **Severity:** 🔴 **P1 (Kritis) & 🟡 P2 (Tinggi)**
- **Berkas Terdampak:**
  - [`resources/views/app/pos/terminal.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/terminal.blade.php)
  - [`lang/id/pos.php`](file:///c:/laragon/www/cooca_core/lang/id/pos.php)
  - [`lang/en/pos.php`](file:///c:/laragon/www/cooca_core/lang/en/pos.php)
- **Rincian Implementasi:**
  1. Tambahkan direktif `@keydown.enter.prevent` pada modal checkout, pasang guard `if (this.isProcessing) return;` di `submitCheckout()`, dan nonaktifkan tombol dengan `:disabled="isProcessing"`.
  2. Pasang fungsi sanitasi `sanitizeSplitAmount(idx)` (`Math.max(0, val)`) dan batasi maksimal 5 metode pembayaran per transaksi.
  3. Sematkan Apple HIG Security Trust Badge dan No-Panic Microcopy (`pos.supervisor_pin_security_note` & `pos.supervisor_pin_no_panic_guide`) pada modal otorisasi supervisor serta bersihkan variabel PIN dari memory saat modal ditutup.
- **Verifikasi & Test:** `tests/Feature/Pos/PosAuditPhase9FraudPreventionAndMicrocopyTest.php`

---

### 🔹 Fase 10: Pengujian Akseptansi Otomatis (Acceptance Test Suite) & Sinkronisasi Dokumen 3-Layer (`F-01` s/d `F-22`)
- **Tujuan Bisnis:** Memverifikasi 100% kelulusan seluruh skenario uji otomatis tanpa regresi dan menyinkronkan seluruh artefak dokumentasi 3-layer.
- **Severity:** 🟢 **P3 (Validasi & Dokumentasi)**
- **Berkas Terdampak:**
  - `tests/Feature/Pos/PosComprehensiveAuditRemediationTest.php`
  - [`docs/system/INDEX.md`](file:///c:/laragon/www/cooca_core/docs/system/INDEX.md)
  - [`docs/AiWorkHistory.md`](file:///c:/laragon/www/cooca_core/docs/AiWorkHistory.md)
  - [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md)
- **Rincian Implementasi:**
  1. Jalankan test suite terpadu `PosComprehensiveAuditRemediationTest.php` yang menguji ke-22 temuan audit secara hulu-ke-hilir.
  2. Jalankan seluruh test suite POS di direktori `tests/Feature/Pos/` dan pastikan hasil: **66/66 Passed, 0 Failures, 0 Errors (100% Zero Regression)**.
  3. Catat entri riwayat pekerjaan terperinci di `docs/AiWorkHistory.md` dan perbarui indeks `docs/system/INDEX.md`.
- **Verifikasi & Test:** `php vendor/bin/phpunit tests/Feature/Pos`

---

## 📊 3. Matriks Berkas Terdampak Hulu-ke-Hilir Lintas 10 Fase

| No | Berkas / Modul Target | Fase Terdampak | Peran & Perubahan Arsitektur |
| :---: | :--- | :---: | :--- |
| 1 | [`app/Domain/Pos/PosShiftService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Pos/PosShiftService.php) | Fase 1 | Perbaikan net cash sales & eliminasi phantom cash deficit shift |
| 2 | [`app/Domain/Pos/PosOrderService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Pos/PosOrderService.php) | Fase 1 | Pembalikan kas ledger & auto-journal saat order void / refund |
| 3 | [`app/Http/Requests/Pos/PosCheckoutRequest.php`](file:///c:/laragon/www/cooca_core/app/Http/Requests/Pos/PosCheckoutRequest.php) | Fase 2 | Validasi skema item, split payment, dan atribut industri |
| 4 | [`app/Models/Business.php`](file:///c:/laragon/www/cooca_core/app/Models/Business.php) | Fase 3 | Helper identifikasi 20 sektor industri (`isPharmacy`, `isLaundry`, dll.) |
| 5 | [`lang/id/pos.php`](file:///c:/laragon/www/cooca_core/lang/id/pos.php) & [`lang/en/pos.php`](file:///c:/laragon/www/cooca_core/lang/en/pos.php) | Fase 4, 9 | Kamus modular 11 domain terminologi POS dwibahasa ID & EN |
| 6 | [`resources/views/app/pos/terminal.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/terminal.blade.php) | Fase 3, 5, 7, 8, 9 | Auto-hiding industri, lokalisasi blade, anti-zoom iOS, smart polling, split clamp |
| 7 | [`resources/views/app/pos/printers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/printers/index.blade.php) | Fase 5, 6 | Unifikasi header `<x-module-header>` & navigasi `<x-module-tabs>` |
| 8 | [`resources/views/app/pos/prep_sheet.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/prep_sheet.blade.php) | Fase 5, 6, 8 | Unifikasi header & standarisasi palet Apple HIG `#34C759` |
| 9 | [`resources/views/app/pos/shifts.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/shifts.blade.php) | Fase 5, 6 | Eliminasi duplikasi headerTitle & lokalisasi shift summary |
| 10 | [`resources/views/app/pos/tables.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/tables.blade.php) | Fase 1, 5 | Perbaikan error handling AJAX delete table & lokalisasi |
| 11 | [`resources/views/app/pos/kitchen.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/kitchen.blade.php) | Fase 5, 7, 8 | Smart polling KDS `document.hidden` & target sentuh 48px |
| 12 | [`resources/views/app/pos/orders.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/orders.blade.php) | Fase 5, 7 | Target sentuh tombol 48px & lokalisasi riwayat order |
| 13 | [`resources/views/app/pos/reports.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/reports.blade.php) | Fase 5 | Lokalisasi laporan kasir & export excel |
| 14 | [`resources/views/app/pos/receipt.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/receipt.blade.php) | Fase 5 | Tabular-nums monospace & tag HTML dinamis |
| 15 | [`resources/views/app/pos/qr-card.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/qr-card.blade.php) | Fase 5 | Tag HTML dinamis & lokalisasi kartu QR meja |
| 16 | [`app/Support/Navigation/NavigationRegistry.php`](file:///c:/laragon/www/cooca_core/app/Support/Navigation/NavigationRegistry.php) | Fase 6 | Pendaftaran sub-tab `printers` di bawah modul `pos` |
| 17 | `tests/Feature/Pos/PosComprehensiveAuditRemediationTest.php` | Fase 10 | Master Acceptance Test Suite (11 test methods, 46 assertions) |

---

## 🎯 4. Definition of Done (DoD) Per Fase

- [x] **Fase 1 DoD:** Test kalkulasi net cash sales, pembalikan kas & jurnal void/refund, dan penanganan status error meja lolos 100%.
- [x] **Fase 2 DoD:** Seluruh payload checkout tervalidasi via FormRequest dan mengembalikan HTTP 422 JSON jika gagal.
- [x] **Fase 3 DoD:** Fitur SPK bengkel, laundry kiloan, dan obat apotek 100% tersembunyi pada tenant F&B/Retail umum.
- [x] **Fase 4 DoD:** 160+ key kamus terjemahan `lang/id/pos.php` dan `lang/en/pos.php` 100% sinkron tanpa key hilang.
- [x] **Fase 5 DoD:** 10 berkas Blade POS bebas dari string hardcoded bahasa Indonesia dan mendukung `window.COOCA_I18N`.
- [x] **Fase 6 DoD:** Seluruh halaman POS memiliki 3-baris page header seragam dan tab printer terdaftar di navigasi.
- [x] **Fase 7 DoD:** Form input mobile berukuran minimal 16px (anti auto-zoom iOS) dan tombol aksi minimal 48x48px.
- [x] **Fase 8 DoD:** Palet Bento Apple HIG seragam `#34C759`, status online/offline aktif, dan polling pause saat `document.hidden`.
- [x] **Fase 9 DoD:** Anti double-submit enter aktif, nominal split payment terlindungi batas minimum, dan microcopy supervisor PIN tampil menenangkan.
- [x] **Fase 10 DoD:** Seluruh 71 test suite POS di `tests/Feature/Pos/` (575 assertions) lolos 100% (0 errors, 0 failures) dan dokumentasi 3-layer tersinkronisasi.
