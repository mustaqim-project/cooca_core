# Master Implementation Plan: Remediasi Ekosistem Purchase Orders Multi-Tenant COOCA (Fase 1 – 10)

**Dokumen Standar Layer 2:** [`docs/system/audits/purchase-orders-master-implementation-plan.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/purchase-orders-master-implementation-plan.md)  
**Rujukan Laporan Audit:** [`docs/system/audits/purchase-orders-comprehensive-audit.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/purchase-orders-comprehensive-audit.md)  
**Status:** `READY FOR EXECUTION & VERIFICATION (10 Phased Roadmap)`

---

## 📋 1. Ringkasan Eksekutif Rencana Kerja

Rencana implementasi master ini dirancang secara sistematis untuk mengeksekusi perbaikan dan hardening terhadap **20 Temuan Faktual (`F-01` s/d `F-20`)** pada seluruh modul Purchase Orders ([`resources/views/app/purchase-orders/`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders)) dan rantai 11 simpul eksekusi backend secara bertahap, terisolasi, dan terverifikasi tanpa regresi.

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│             ROADMAP MASTER IMPLEMENTASI PURCHASE ORDERS COOCA (10 FASE TERPADU)                  │
├──────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Fase 1: Hardening Validasi Request & Multi-Tenant IDOR Shield (F-01, F-02)                       │
│ Fase 2: Atomic DB Transactions & Non-Destructive Fair Quota Metering (F-04, F-05)                │
│ Fase 3: Three-Way Matching & Supervisor PIN Bcrypt Guardrail Pembatalan (F-03, F-15, F-17)        │
│ Fase 4: Dynamic Context-Aware Auto-Hiding 20 Sektor Industri (F-07, F-16, F-20)                  │
│ Fase 5: Bento Segmented Control Tabs, Deep-Linking & Unifikasi Header 3-Baris (F-08, F-18)       │
│ Fase 6: Mobile-First Responsive Overhaul, Anti-Safari Zoom & Ergonomic Tap Targets (F-09,10,11)  │
│ Fase 7: Penyempurnaan 1-Klik Beli ke Stok: Bahan Mentah Pasar & Cash Ledger Outflow (F-13)      │
│ Fase 8: Ekstraksi Kamus Dwibahasa Penuh & Lokalisasi Blade (lang/id & lang/en) (F-06, F-19)     │
│ Fase 9: Double-Submit Protection & Audit Log Non-Repudiation (F-12, F-14, F-15)                  │
│ Fase 10: Pengujian Otomatis Akseptansi (Feature Test Suite) & Sinkronisasi Layer 2 Docs          │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ 2. Rincian Teknis Eksekusi Per Fase (Fase 1 – 10)

### 🔹 Fase 1: Hardening Validasi Request & Multi-Tenant IDOR Shield (`F-01`, `F-02`)
- **Tujuan Bisnis:** Mengeliminasi celah manipulasi relasi pelanggan, vendor, produk, dan material antar-tenant pada form pembuatan PO dan penerimaan barang fisik.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas Terdampak:**
  - [`app/Http/Controllers/Web/PurchaseOrderWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php)
  - [`app/Http/Controllers/Web/Purchasing/GoodsReceiptWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Purchasing/GoodsReceiptWebController.php)
- **Tindakan Perbaikan:**
  - Pasang `Rule::exists('table', 'id')->where('business_id', $businessId)` pada seluruh aturan validasi form.
  - Pasang `abort_unless($purchaseOrder->business_id === $business->id, 403);` di baris pertama seluruh method controller.

---

### 🔹 Fase 2: Atomic DB Transactions & Non-Destructive Fair Quota Metering (`F-04`, `F-05`)
- **Tujuan Bisnis:** Mencegah terciptanya header PO yatim saat insert item gagal dan melindungi kuota bulanan merchant Free dari pemotongan yang tidak adil jika terjadi crash.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas Terdampak:**
  - [`app/Domain/Commerce/PurchaseOrderService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Commerce/PurchaseOrderService.php)
  - [`app/Http/Controllers/Web/PurchaseOrderWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php)
- **Tindakan Perbaikan:**
  - Bungkus `createPurchaseOrder` ke dalam `DB::transaction(...)`.
  - Pindahkan `incrementMonthlyUsage` ke blok akhir setelah closure transaksi berhasil di-commit.

---

### 🔹 Fase 3: Three-Way Matching & Supervisor PIN Bcrypt Guardrail (`F-03`, `F-15`, `F-17`)
- **Tujuan Bisnis:** Mencegah fraud penggelapan inventaris melalui pembatalan sepihak atas PO yang barang fisiknya telah diterima atau fakturnya telah diterbitkan.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas Terdampak:**
  - [`app/Http/Controllers/Web/PurchaseOrderWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php)
  - [`resources/views/app/purchase-orders/show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/show.blade.php)
- **Tindakan Perbaikan:**
  - Tolak pembatalan jika `$purchaseOrder->goodsReceipts()->exists()` atau `$purchaseOrder->invoices()->exists()`.
  - Wajibkan otorisasi `Hash::check($pin, $business->pos_supervisor_pin)` untuk PO yang telah berstatus `confirmed`.
  - Batalkan approval request yang menggantung dan catat mutasi status ke `AuditLog`.

---

### 🔹 Fase 4: Dynamic Context-Aware Auto-Hiding 20 Sektor Industri (`F-07`, `F-16`, `F-20`)
- **Tujuan Bisnis:** Menyembunyikan fitur dan filter PO Pelanggan (B2B) pada sektor industri yang tidak relevan (seperti F&B Restoran/Kafe dan Ritel), mencegah kebingungan operasional kasir UMKM.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas Terdampak:**
  - [`resources/views/app/purchase-orders/create.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php)
  - [`resources/views/app/purchase-orders/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php)
- **Tindakan Perbaikan:**
  - Bungkus tab switcher tipe PO dan filter tipe dengan `@if($canCustomerPo)`.
  - Set default tipe transaksi ke `supplier` jika modul `customer_po` nonaktif.

---

### 🔹 Fase 5: Bento Segmented Control Tabs & Unifikasi Header 3-Baris (`F-08`, `F-18`)
- **Tujuan Bisnis:** Menghilangkan full-page reloads saat berganti filter status dokumen dan menyelaraskan arsitektur header dengan desain Bento Apple HIG v2.0.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas Terdampak:**
  - [`resources/views/app/purchase-orders/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php)
  - [`resources/views/app/purchase-orders/show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/show.blade.php)
- **Tindakan Perbaikan:**
  - Ganti elemen HTML `<select onchange="submit()">` dengan tab bar Segmented Control Bento HIG dengan deep-linking query `?status=...`.
  - Standarisasi header dokumen detail menjadi struktur 3-baris.

---

### 🔹 Fase 6: Mobile-First Responsive Overhaul & Anti-Safari Zoom (`F-09`, `F-10`, `F-11`)
- **Tujuan Bisnis:** Mencegah auto-zoom paksa pada Safari iOS iPhone dan menghilangkan trap scroll horizontal pada tabel form di layar 360px–390px.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas Terdampak:**
  - [`resources/views/app/purchase-orders/create.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php)
  - [`resources/views/app/purchase-orders/show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/show.blade.php)
  - [`resources/views/app/purchase-orders/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php)
- **Tindakan Perbaikan:**
  - Terapkan layout Dual View: Mobile Card View (`sm:hidden`) dan Desktop Table (`hidden sm:block`).
  - Ubah seluruh kelas font form menjadi `text-[16px] sm:text-[13px]`.
  - Tingkatkan ukuran tap targets menjadi minimum `min-h-[44px] min-w-[44px]`.

---

### 🔹 Fase 7: Penyempurnaan 1-Klik Beli ke Stok (Bahan Baku & Kas Keluar) (`F-13`)
- **Tujuan Bisnis:** Mendukung pengadaan cepat bahan mentah dapur dan bengkel di pasar tradisional serta membukukan kas keluar pada buku kas laci.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas Terdampak:**
  - [`app/Http/Controllers/Web/Purchasing/GoodsReceiptWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Purchasing/GoodsReceiptWebController.php)
  - [`resources/views/app/purchase-orders/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php)
- **Tindakan Perbaikan:**
  - Tambahkan toggle pemilihan entitas (Produk vs Bahan Baku) pada modal cepat.
  - Panggil `CashLedgerService::recordOutflow` jika akun kas dipilih.

---

### 🔹 Fase 8: Ekstraksi Kamus Dwibahasa Penuh & Lokalisasi Blade (`F-06`, `F-19`)
- **Tujuan Bisnis:** Memastikan modul Purchase Orders 100% dwibahasa (Bahasa Indonesia & English) tanpa hardcoded strings.
- **Severity:** 🔴 **P1 (Kritis)**
- **Berkas Terdampak:**
  - [`lang/id/purchasing.php`](file:///c:/laragon/www/cooca_core/lang/id/purchasing.php)
  - [`lang/en/purchasing.php`](file:///c:/laragon/www/cooca_core/lang/en/purchasing.php)
  - Seluruh berkas Blade `resources/views/app/purchase-orders/`.
- **Tindakan Perbaikan:**
  - Buat kamus dwibahasa 140+ string dan ganti seluruh teks Blade dengan `{{ __('purchasing.key') }}`.

---

### 🔹 Fase 9: Double-Submit Protection & Audit Log Non-Repudiation (`F-12`, `F-14`, `F-15`)
- **Tujuan Bisnis:** Melindungi sistem dari pembuatan PO duplikat akibat double click tombol kirim dan menyediakan audit trail mutasi dokumen.
- **Severity:** 🟡 **P2 (Tinggi)**
- **Berkas Terdampak:**
  - [`resources/views/app/purchase-orders/create.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php)
  - [`resources/views/app/purchase-orders/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php)
  - [`app/Http/Controllers/Web/PurchaseOrderWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php)
- **Tindakan Perbaikan:**
  - Pasang state `:disabled="isSubmitting"` dengan spinner visual.
  - Catat log mutasi `po.created`, `po.confirmed`, `po.cancelled`, `po.deleted` ke `AuditLog`.

---

### 🔹 Fase 10: Pengujian Otomatis Akseptansi & Sinkronisasi Layer 2 Docs
- **Tujuan Bisnis:** Membuktikan bahwa seluruh skenario (IDOR, Atomicity, Supervisor PIN, Auto-Hiding, i18n, Three-Way Matching) teruji secara otomatis dengan status 100% lolos tanpa regresi.
- **Severity:** 🟢 **P3 (Verifikasi Mutu)**
- **Berkas Terdampak:**
  - `tests/Feature/PurchaseOrderWebControllerTest.php`
  - [`docs/system/INDEX.md`](file:///c:/laragon/www/cooca_core/docs/system/INDEX.md)
  - [`docs/system/audits/purchase-orders-comprehensive-audit.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/purchase-orders-comprehensive-audit.md)
