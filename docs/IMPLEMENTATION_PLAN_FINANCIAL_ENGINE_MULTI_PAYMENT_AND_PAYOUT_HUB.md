# Rencana Implementasi Bertahap: Arsitektur Finansial Terpadu, Multi-Payment POS, Manajemen Mesin EDC, Cooca Pay Payout Hub & Rekonsiliasi Saldo Multi-Cabang

> **ID Dokumen:** `PLAN-16-FINANCIAL-ENGINE-MULTI-PAYMENT-AND-PAYOUT-HUB`  
> **Status:** APPROVED & READY TO EXECUTE (Rencana Resmi Disetujui)  
> **PRD Terkait:** [`docs/prd/PRD-16-UNIFIED-FINANCIAL-ENGINE-MULTI-PAYMENT-SETTLEMENT-AND-PAYOUT-HUB.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-16-UNIFIED-FINANCIAL-ENGINE-MULTI-PAYMENT-SETTLEMENT-AND-PAYOUT-HUB.md)  
> **Audit Rujukan:** [`docs/system/architecture/financial-engine-multi-payment-and-settlement-audit.md`](file:///c:/laragon/www/cooca_core/docs/system/architecture/financial-engine-multi-payment-and-settlement-audit.md)  
> **Standar Rekayasa:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md), Bento Apple HIG v2.0  

---

## 1. Ringkasan Eksekutif & Sasaran Perbaikan

Rencana ini merinci langkah-langkah implementasi teknis untuk membangun sistem keuangan terpadu di COOCA yang mengintegrasikan transaksi kasir fisik POS, transaksi digital Cooca Pay (QR Order Meja & Toko Online), saluran *Food Delivery* (GoFood, GrabFood, ShopeeFood), dan *Marketplace* (Shopee, TikTok Shop, Tokopedia) ke dalam satu ekosistem pembukuan yang transparan, aman, dan terotomasi.

```text
┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│ FASE 1: Whitelabeling & Penyempurnaan UI Menu QR Order Meja (P1 - Kritis)                       │
│         - Refactor copywriting & lencana metode pembayaran pada menu.blade.php                  │
│         - Pemisahan visual: Bayar QRIS Cooca Pay (Instant) vs Bayar di Kasir (Manual)           │
│         - Whitelabeling 100% (eliminasi seluruh referensi vendor gateway mentah di sisi klien)  │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                ↓                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 2: Fondasi Basis Data & Master Profil Rekening Penarikan & Mesin EDC (P1 - Kritis)        │
│         - Migrasi tabel: merchant_payout_bank_accounts, store_edc_terminals                     │
│         - Implementasi Strict Owner Identity Matching (Anti-Third-Party Payout Guard)           │
│         - Model Eloquent, casts, relasi multi-cabang (location_id) & policy otorisasi           │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                ↓                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 3: POS Multi-Payment Engine & Split Payment UI Kasir (P1 - Kritis)                         │
│         - Antarmuka interaktif Split Payment di Terminal Kasir POS (terminal.blade.php)         │
│         - Validasi sisa tagihan, input multi-metode, penanganan kembalian tunai                 │
│         - Pembaruan Struk Thermal ESC/POS (receipt.blade.php) & Compound Auto-Journaling        │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                ↓                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 4: Cooca Pay Payout Hub Dua Sisi (Merchant & SuperAdmin Portal) (P1 - Kritis)             │
│         - Refactor halaman settlement merchant menjadi Cooca Pay Payout Hub                     │
│         - Fitur Auto-Payout H+1 Terjadwal (09:00 WIB) & On-Demand Manual Request                │
│         - Portal SuperAdmin Cooca: Approval, upload slip transfer bank & notifikasi WhatsApp    │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                ↓                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 5: Lembar Rekonsiliasi Saldo Eksternal & Dashboard Likuiditas Multi-Cabang (P2 - Tinggi)   │
│         - Migrasi tabel: external_account_reconciliations                                       │
│         - Form rekonsiliasi bulanan GoBiz, GrabMerchant, Shopee Seller, TikTok Shop & EDC       │
│         - Bento Dashboard 'Where The Money Lives' (Omzet, HPP, Laba, & Peta 6 Titik Likuiditas) │
│         - Filter konsolidasi All Branches vs Cabang Tertentu & Suite Pengujian 100% Lolos       │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Inventaris Berkas Kode Terlibat (Codebase Mapping)

### A. Berkas yang Akan Dibuat Baru (*Files to be Generated*):
1. **Migrasi Basis Data:**
   * `database/migrations/2026_09_28_150000_create_merchant_payout_bank_accounts_table.php`
   * `database/migrations/2026_09_28_151000_create_store_edc_terminals_table.php`
   * `database/migrations/2026_09_28_152000_create_external_account_reconciliations_table.php`
2. **Model Eloquent:**
   * `app/Models/MerchantPayoutBankAccount.php`
   * `app/Models/StoreEdcTerminal.php`
   * `app/Models/ExternalAccountReconciliation.php`
3. **Domain Services & Actions:**
   * `app/Domain/Finance/MerchantPayoutAccountService.php` (Validasi *Strict Owner Identity Matching*)
   * `app/Domain/Finance/StoreEdcTerminalService.php` (Manajemen Mesin EDC per Cabang)
   * `app/Domain/Finance/ExternalReconciliationService.php` (Kalkulasi & Deteksi Selisih Rekonsil)
4. **Controllers:**
   * `app/Http/Controllers/Web/Finance/MerchantPayoutAccountWebController.php`
   * `app/Http/Controllers/Web/Finance/StoreEdcTerminalWebController.php`
   * `app/Http/Controllers/Web/Finance/ExternalReconciliationWebController.php`
5. **Views (Bento Apple HIG Blade):**
   * `resources/views/app/finance/payout-accounts/index.blade.php` (Master Rekening Penarikan)
   * `resources/views/app/finance/edc-terminals/index.blade.php` (Master Mesin EDC Cabang)
   * `resources/views/app/finance/reconciliation/index.blade.php` (Lembar Rekonsiliasi Saldo Eksternal)
   * `resources/views/app/finance/reconciliation/show.blade.php` (Detail Rekonsiliasi Bulanan)
6. **Automated Feature Tests:**
   * `tests/Feature/Finance/MerchantPayoutAccountSecurityTest.php`
   * `tests/Feature/Pos/PosMultiPaymentSplitTest.php`
   * `tests/Feature/Finance/ExternalAccountReconciliationTest.php`
   * `tests/Feature/Finance/MultiBranchFinancialIsolationTest.php`

### B. Berkas Existing yang Akan Dimodifikasi (*Files to be Modified*):
1. **Antarmuka Pengguna & Blade Views:**
   * `resources/views/public/qr-order/menu.blade.php`: Penyempurnaan kartu pilihan pembayaran (Bayar QRIS Cooca Pay vs Bayar di Kasir).
   * `resources/views/app/pos/terminal.blade.php`: Penambahan modal interaktif Split Payment (Multi-Payment) di kasir POS.
   * `resources/views/app/pos/receipt.blade.php`: Penyesuaian struk thermal ESC/POS untuk mencetak rincian multi-payment.
   * `resources/views/app/finance/settlements/index.blade.php`: Transformasi view settlement menjadi **Cooca Pay Payout Hub**.
   * `resources/views/app/finance/cash-bank/index.blade.php`: Penambahan Bento Box Eksekutif *"Where The Money Lives"*.
   * `resources/views/components/module-tabs.blade.php`: Penambahan tab sekunder pada modul finance.
2. **Backend Services & Domain Engine:**
   * `app/Domain/Pos/PosOrderService.php`: Penyempurnaan pemrosesan array `paymentsData` multi-metode, pencatatan EDC TID, dan auto-journal.
   * `app/Domain/Finance/PaymentSettlementService.php`: Penambahan fitur scheduling auto-payout H+1 dan pengaitan ke `merchant_payout_bank_accounts`.
   * `app/Domain/Accounting/AutoJournalService.php`: Penambahan formula jurnal majemuk (*compound journal*) untuk split payment dan fee Cooca Pay.
3. **Controllers Existing:**
   * `app/Http/Controllers/Web/Finance/CashLedgerWebController.php`: Integrasi konsolidasi likuiditas multi-cabang.
   * `app/Http/Controllers/Web/Finance/PaymentSettlementWebController.php`: Integrasi rekening penarikan terverifikasi.
   * `app/Http/Controllers/Admin/AdminSettlementController.php`: Integrasi komparasi nama pemilik usaha vs nama rekening penerima transfer.
4. **Rute Sistem:**
   * `routes/owner.php`: Pendaftaran rute payout accounts, EDC terminals, dan external reconciliation dengan proteksi RBAC.
   * `routes/admin.php`: Penyesuaian rute superadmin settlement.

---

## 3. Hierarki Posisi Menu & Navigasi Antarmuka

```text
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│ SIDEBAR UTAMA (COOCA BOS V2)                                                                     │
├──────────────────────────────────────────────────────────────────────────────────────────────────┤
│ ├── 📊 Ringkasan Eksekutif (Dashboard)                                                           │
│ ├── 🛒 Operasional Kasir (POS)                                                                   │
│ ├── 📦 Produk & Inventori                                                                        │
│ ├── 💰 Keuangan & Akuntansi (Finance Hub) ➔ [ KLIK DISINI ]                                      │
│ │   ├── Tab 1: Ringkasan Kas & Likuiditas ("Where The Money Lives" Dashboard)                   │
│ │   ├── Tab 2: Buku Kas & Ledger (Mutasi Inflow/Outflow)                                         │
│ │   ├── Tab 3: Cooca Pay Payout Hub (Penarikan Dana QRIS & Toko Online)                          │
│ │   ├── Tab 4: Rekonsiliasi Saldo Eksternal (GoBiz, Grab, Shopee, EDC, Kasir)                    │
│ │   ├── Tab 5: Jurnal Akuntansi (Double-Entry General Ledger)                                    │
│ │   └── Tab 6: Piutang & Hutang (AR/AP Management)                                               │
│ └── ⚙️ Pengaturan Bisnis (Settings Hub)                                                          │
│     ├── Rekening Penarikan Terverifikasi (Strict Owner Verified Bank)                            │
│     ├── Master Terminal Mesin EDC Cabang (BCA, Mandiri, BRI, dll.)                               │
│     └── QRIS Statis & Rekening Transfer Toko                                                     │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 4. Matriks Hak Akses & Otorisasi Peran (RBAC Permission Matrix)

| Izin Sistem (*Permission*) | SuperAdmin Cooca | Business Owner (HQ) | Finance Manager | Store Manager Cabang | Kasir POS | Pelanggan Meja |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| `finance.view` | ✅ | ✅ | ✅ | ✅ (Cabangnya) | ❌ | ❌ |
| `finance.manage` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `finance.payout.request` | ❌ | ✅ | ✅ (Jika Diizinkan) | ❌ | ❌ | ❌ |
| `finance.payout.approve_admin` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `finance.reconcile_external` | ❌ | ✅ | ✅ | ✅ (Cabangnya) | ❌ | ❌ |
| `pos.split_payment` | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ |
| `settings.payout_bank.manage`| ❌ | ✅ (Strict Owner) | ❌ | ❌ | ❌ | ❌ |
| `settings.edc_terminals.manage`| ❌ | ✅ | ✅ | ✅ (Cabangnya) | ❌ | ❌ |
| `qr_order.pay_cooca_qris` | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 5. Rancangan Antarmuka Bento Apple HIG v2.0 & Pengalaman Pengguna (UI/UX)

### 5.1 Modal Sheet Split Payment di Terminal Kasir POS (`terminal.blade.php`)
* **Ukuran Modal:** Full-Size XXL (`max-w-5xl xl:max-w-6xl`) dengan latar *frosted glass vibrancy*.
* **Kolom Kiri (Input Pembayaran Majemuk):**
  - Ringkasan Total Tagihan dengan tipografi SF Pro `tabular-nums` tebal (`text-[28px] text-[#007AFF]`).
  - Baris pembayaran dinamis dengan tombol `[ + Tambah Metode ]`:
    - Row 1: Dropdown `Tunai (Cash)` $\rightarrow$ Input Rp 100.000.
    - Row 2: Dropdown `EDC BCA (TID: 8821)` $\rightarrow$ Input Rp 300.000 + Input Approval Code.
    - Row 3: Dropdown `Cooca Pay QRIS` $\rightarrow$ Input Rp 100.000.
* **Kolom Kanan (Kalkulasi Likuiditas & Status Lunas):**
  - Total Terbayar: `Rp 500.000` (Hijau `#34C759`).
  - Sisa Tagihan: `Rp 0` (Lunas).
  - Kembalian: `Rp 0`.
  - Tombol Aksi Utama: `[ Selesaikan Transaksi & Cetak Struk (F10) ]` (Tinggi 48px, Apple Blue `#007AFF`).

### 5.2 Form Pendaftaran Rekening Penarikan (*Strict Owner Matching*)
* **Ukuran Modal:** Modal Sheet XXL (`max-w-4xl`).
* **Proteksi Visual Anti-Fraud:**
  - Banner informasi biru Apple:
    > *"Demi keamanan dana dan regulasi anti-pencucian uang, nama pemilik rekening penarikan WAJIB SAMA PERSIS dengan nama pemilik usaha terdaftar: **[ NAMA PEMILIK USAHA ]**. Penarikan ke rekening pihak ketiga/karyawan tidak diizinkan."*
  - Kolom Input:
    1. Pilih Bank Penerbit (BCA, Mandiri, BRI, BNI, Bank Jago, SeaBank, Permata, dll.).
    2. Nomor Rekening.
    3. Nama Pemilik Rekening (Sistem memvalidasi kecocokan karakter secara *real-time*).
    4. Centang `[x] Jadikan Rekening Utama Pencairan Otomatis H+1`.

### 5.3 Lembar Rekonsiliasi Saldo Eksternal Bulanan (`/finance/reconciliation`)
* **Struktur Tabel Konsolidasi:**
  - Menampilkan baris kanal: *GoFood (GoBiz)*, *GrabFood*, *ShopeeFood*, *Shopee Seller*, *TikTok Shop*, *EDC BCA*, *Laci Kasir Fisik*.
  - Kolom Data: `Saldo Awal` | `+ Inflow POS` | `- Penarikan ke Bank` | `Saldo Ekspektasi` | `Saldo Sisa Riil Aplikasi` | `Selisih (Discrepancy)` | `Status`.
  - Status Badge:
    - Hijau `MATCH`: Jika selisih `Rp 0`.
    - Merah/Kuning `SELISIH`: Jika ada perbedaan angka $\rightarrow$ Tombol `[ Catat Penyesuaian Selisih ]`.

---

## 6. Rincian 5 Fase Implementasi Bertahap

### FASE 1: Whitelabeling & Penyempurnaan UI Menu QR Order Meja (P1 - Kritis)

1. **Langkah 1.1: Refactor Copywriting & Visual pada `menu.blade.php`**
   * Berkas: [`resources/views/public/qr-order/menu.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/public/qr-order/menu.blade.php)
   * Ubah kartu pembayaran menjadi 2 opsi Bento terstandarisasi:
     - **Opsi A (Bayar Langsung via QRIS Cooca Pay):** Ikon Lucide `qr-code`, copy: *"Scan instan di HP • Otomatis diproses dapur • Bebas antre di kasir"*, lencana hijau *"Bebas Biaya Admin"*.
     - **Opsi B (Bayar di Kasir):** Ikon Lucide `store`, copy: *"Bayar ke kasir sebelum atau sesudah makan"*, subteks *"Tunai / Kartu EDC / QRIS Kasir"*.
   * Pastikan tidak ada istilah vendor gateway mentah yang muncul di sisi pelanggan.

---

### FASE 2: Fondasi Basis Data & Master Profil Rekening Penarikan & Mesin EDC (P1 - Kritis)

1. **Langkah 2.1: Pembuatan Migration DDL Basis Data**
   * Berkas: `database/migrations/2026_09_28_150000_create_merchant_payout_bank_accounts_table.php`
   * Berkas: `database/migrations/2026_09_28_151000_create_store_edc_terminals_table.php`
   * Jalankan migrasi: `php artisan migrate`.
2. **Langkah 2.2: Pembuatan Model Eloquent & Policy**
   * Berkas: `app/Models/MerchantPayoutBankAccount.php` (Relasi ke `Business`, `Location`, scope `isPrimary`).
   * Berkas: `app/Models/StoreEdcTerminal.php` (Relasi ke `Business`, `Location`, scope `activeForLocation`).
3. **Langkah 2.3: Implementasi Service Validasi Strict Owner Name**
   * Berkas: `app/Domain/Finance/MerchantPayoutAccountService.php`
   * Menerapkan logika perbandingan nama KTP pemilik bisnis vs nama pemilik rekening (`cleanOwner === cleanHolder`) dengan penolakan `DomainException` jika berbeda.
4. **Langkah 2.4: Pembuatan Controller & View Master Data**
   * Berkas: `app/Http/Controllers/Web/Finance/MerchantPayoutAccountWebController.php`
   * Berkas: `app/Http/Controllers/Web/Finance/StoreEdcTerminalWebController.php`
   * Berkas: `resources/views/app/finance/payout-accounts/index.blade.php`
   * Berkas: `resources/views/app/finance/edc-terminals/index.blade.php`

---

### FASE 3: POS Multi-Payment Engine & Split Payment UI Kasir (P1 - Kritis)

1. **Langkah 3.1: Pembaruan Antarmuka Kasir POS (`terminal.blade.php`)**
   * Berkas: [`resources/views/app/pos/terminal.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/terminal.blade.php)
   * Tambahkan state Alpine.js `paymentRows: []`, `remainingAmount`, `totalPaidAmount`, `changeAmount`.
   * Integrasikan dropdown Mesin EDC terdaftar sesuai cabang kasir aktif.
2. **Langkah 3.2: Penyesuaian `PosOrderService.php`**
   * Berkas: [`app/Domain/Pos/PosOrderService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Pos/PosOrderService.php)
   * Simpan setiap baris pembayaran ke `pos_order_payments` dengan mencatat `store_edc_terminal_id` dan `reference_number`.
   * Perbarui kalkulasi `CashLedgerService::recordInflow` agar mencatat mutasi bersih per akun kas/EDC masing-masing.
3. **Langkah 3.3: Pembaruan Struk Pembelian Thermal (`receipt.blade.php`)**
   * Berkas: [`resources/views/app/pos/receipt.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/pos/receipt.blade.php)
   * Cetak rincian seluruh metode pembayaran jika transaksi berstatus *Multi-Payment*.
4. **Langkah 3.4: Auto-Journal Majemuk (`AutoJournalService.php`)**
   * Berkas: [`app/Domain/Accounting/AutoJournalService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Accounting/AutoJournalService.php)
   * Pastikan `recordPosSaleJournal` menerbitkan multi-debit ke akun kas, EDC kliring, piutang, dan saldo Cooca Pay secara proporsional.

---

### FASE 4: Cooca Pay Payout Hub Dua Sisi (Merchant & SuperAdmin Portal) (P1 - Kritis)

1. **Langkah 4.1: Refactor View Settlement Merchant**
   * Berkas: [`resources/views/app/finance/settlements/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/settlements/index.blade.php)
   * Ubah judul dan branding menjadi **Cooca Pay Payout Hub**.
   * Integrasikan dropdown rekening bank penarikan terverifikasi dari `merchant_payout_bank_accounts`.
   * Tambahkan pengaturan preferensi: *Mode Auto-Payout H+1 (Setiap Pagi 09:00 WIB)* vs *Mode Penarikan Manual*.
2. **Langkah 4.2: Pembaruan Domain Service Payout (`PaymentSettlementService.php`)**
   * Berkas: [`app/Domain/Finance/PaymentSettlementService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Finance/PaymentSettlementService.php)
   * Tambahkan metode `scheduleAutoPayout(Business $business)` untuk cron job harian.
3. **Langkah 4.3: Penguatan Portal SuperAdmin Cooca (`AdminSettlementController.php`)**
   * Berkas: [`app/Http/Controllers/Admin/AdminSettlementController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Admin/AdminSettlementController.php)
   * Tampilkan kartu komparasi: **Nama Pemilik Usaha (KTP/Akun) vs Nama Rekening Penerima Transfer**.
   * Validasi wajib lampiran foto slip bukti transfer bank (`proof_image`) saat approve.
   * Picu notifikasi WhatsApp resmi via Meta Cloud API ke nomor pemilik usaha saat dana berhasil ditransfer.

---

### FASE 5: Lembar Rekonsiliasi Saldo Eksternal & Dashboard Likuiditas Multi-Cabang (P2 - Tinggi)

1. **Langkah 5.1: Migrasi Basis Data Rekonsiliasi Eksternal**
   * Berkas: `database/migrations/2026_09_28_152000_create_external_account_reconciliations_table.php`
   * Model: `app/Models/ExternalAccountReconciliation.php`
2. **Langkah 5.2: Service & Controller Rekonsiliasi Eksternal**
   * Berkas: `app/Domain/Finance/ExternalReconciliationService.php`
   * Berkas: `app/Http/Controllers/Web/Finance/ExternalReconciliationWebController.php`
   * Berkas: `resources/views/app/finance/reconciliation/index.blade.php`
3. **Langkah 5.3: Bento Dashboard "Where The Money Lives" (`cash-bank/index.blade.php`)**
   * Berkas: [`resources/views/app/finance/cash-bank/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/finance/cash-bank/index.blade.php)
   * Tampilkan Bento Box Konsolidasian: Omzet Kotor, HPP Riil, Laba Bersih, dan Peta Sebaran Saldo 6 Titik Likuiditas dengan filter cabang.
4. **Langkah 5.4: Pengujian Otomatis Komprehensif (Automated Feature Tests)**
   * Eksekusi seluruh test suite: `php artisan test --filter=Finance` dan `php artisan test --filter=Pos`.
   * Pastikan 100% test lulus tanpa failure/error.

---

## 7. Rencana Pengujian Otomatis & Verifikasi (Test Suite Matrix)

| Test File | Skenario Pengujian | Hasil yang Diharapkan |
| :--- | :--- | :---: |
| `MerchantPayoutAccountSecurityTest.php` | Daftarkan rekening dengan nama sama dengan owner | `PASS` (Status: Verified) |
| `MerchantPayoutAccountSecurityTest.php` | Daftarkan rekening dengan nama pihak ketiga / orang lain | `PASS` (Ditolak Exception) |
| `PosMultiPaymentSplitTest.php` | Transaksi POS dengan 3 metode (Tunai + EDC + QRIS) | `PASS` (Semua Saldo & Jurnal Pas) |
| `PosMultiPaymentSplitTest.php` | Transaksi tunai lebih bayar $\rightarrow$ kembalian akurat | `PASS` (Kas Laci Bersih) |
| `ExternalAccountReconciliationTest.php` | Input saldo awal + Inflow POS - Payout = Sisa Saldo | `PASS` (Match 100%) |
| `ExternalAccountReconciliationTest.php` | Saldo aktual aplikasi beda dengan hitungan sistem | `PASS` (Status: Discrepancy) |
| `MultiBranchFinancialIsolationTest.php` | Kasir Cabang JKT akses akun Cabang BDG | `PASS` (403 Forbidden / Isolated) |
| `MultiBranchFinancialIsolationTest.php` | Owner HQ lihat konsolidasi saldo seluruh cabang | `PASS` (Data Gabungan Lengkap) |

---

## 8. Definition of Done (DoD) Checklist

- [ ] Seluruh migrasi database (`merchant_payout_bank_accounts`, `store_edc_terminals`, `external_account_reconciliations`) selesai dieksekusi bersih.
- [ ] Whitelabeling Cooca Pay terpasang 100% di UI tanpa residu nama vendor pihak ketiga.
- [ ] Validasi *Strict Owner Identity Matching* aktif dan memblokir pendaftaran rekening pihak ketiga.
- [ ] Transaksi Split Payment di POS Terminal berhasil dan menghasilkan struk thermal + jurnal akuntansi yang seimbang.
- [ ] Cooca Pay Payout Hub memiliki opsi Auto-Payout H+1 dan manual request dengan audit trail slip transfer admin.
- [ ] Lembar Rekonsiliasi Saldo Eksternal bulanan berfungsi mendeteksi selisih saldo GoBiz/Shopee/EDC.
- [ ] Dashboard *"Where The Money Lives"* menampilkan peta likuiditas 6 titik dengan filter konsolidasi cabang.
- [ ] Seluruh pengujian otomatis lolos 100% (0 failure, 0 error).
- [ ] Dokumentasi 3-layer (`AiWorkHistory.md`, `docs/system/`, `docs/SYSTEM_GUIDE.md`) diperbarui tuntas.
