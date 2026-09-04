# 📋 PERINTAH DETAIL SETIAP FASE — COOCA UMKM

> Dokumen ini adalah **panduan eksekusi teknis** per fase.  
> Setiap fase berisi: **Konteks → Sub-task → File Target → Command → Hasil yang Diharapkan**.

---

## ✅ FASE 1 — Fondasi Inventori & Stock Service (P0) [HARDENED]

**Tujuan:** Jadikan `StockService` satu-satunya gerbang mutasi stok yang aman, atomic, dan concurrency-safe.

### Sub-task & Hasil

| Sub-task | File Target | Hasil yang Diharapkan |
|----------|-------------|----------------------|
| 1.1 Pessimistic lock pada setiap mutasi | `StockService::recordMovement()` | Tidak ada race condition stok minus saat checkout simultan |
| 1.2 Validasi stok minus | Migration `allow_negative_stock` + `InsufficientStockException` | Sistem melempar exception jika stok < 0 dan bisnis tidak mengizinkan stok minus |
| 1.3 Gateway pemotongan invoice | `StockService::deductForInvoiceSale()` + `restoreForInvoiceReturn()` | Konstanta `TYPE_INVOICE_SALE` dan `TYPE_INVOICE_RETURN` di `StockMovement` |
| 1.4 Anti hard-delete | `InvoiceWebController::destroy()` + `PurchaseOrderWebController::destroy()` | Hanya dokumen `draft` yang bisa dihapus; dokumen aktif → abort 403 |
| 1.5 Unit test | `tests/Unit/StockServiceConcurrencyAndIntegrityTest.php` | **5/5 tests passed** |

### Commands
```bash
php artisan migrate
php artisan test tests/Unit/StockServiceConcurrencyAndIntegrityTest.php
```

---

## ✅ FASE 2 — Integrasi Sales Invoice → Stok & Keuangan (P0) [HARDENED]

**Tujuan:** Setiap Invoice yang dikonfirmasi wajib memotong stok gudang dan mencatat jurnal akuntansi double-entry.

### Sub-task & Hasil

| Sub-task | File Target | Hasil yang Diharapkan |
|----------|-------------|----------------------|
| 2.1 Kolom `location_id` di invoice | Migration + `Invoice` model | Invoice menunjuk gudang asal pengeluaran barang |
| 2.2 State machine confirm & void | `InvoiceService::confirmAndRelease()` + `voidInvoice()` | Draft → Unpaid: stok terpotong; Void → stok kembali |
| 2.3 Auto-journal piutang | `AutoJournalService::recordInvoiceIssuedJournal()` | Jurnal Debit: Piutang (1-1003), Kredit: Pendapatan (4-4001), HPP (5-5001), Persediaan (1-1004) |
| 2.4 Auto-journal pembayaran | `AutoJournalService::recordInvoicePaymentJournal()` | Jurnal Debit: Kas/Bank (1-1001/1-1002), Kredit: Piutang (1-1003) |
| 2.5 Route confirm & void | `routes/web.php` | `POST /invoices/{id}/confirm` & `POST /invoices/{id}/void` |
| 2.6 UI dropdown gudang | `invoices/create.blade.php` | Dropdown pilih gudang asal; default gudang utama |
| 2.7 UI tombol aksi | `invoices/show.blade.php` | Tombol Konfirmasi (saat draft), Void (saat belum bayar), badge nama gudang |
| 2.8 Integration test | `tests/Feature/InvoiceStockAndJournalIntegrationTest.php` | **6/6 tests passed** (14 assertions) |

### Commands
```bash
php artisan migrate
php artisan test tests/Feature/InvoiceStockAndJournalIntegrationTest.php
```

---

## ✅ FASE 3 — Purchasing, Receiving & Manajemen Hutang Supplier (P1) [SELESAI]

**Tujuan:** Setiap Goods Receipt yang dikonfirmasi wajib mencatat Hutang Usaha ke General Ledger dan memiliki alur pelunasan ke supplier.

### Task Rencana Eksekusi

**Keputusan desain:** Goods Receipt menjadi transaksi posting penerimaan. Web dan API wajib menggunakan service yang sama, seluruh posting dibungkus transaksi database, dan setiap Goods Receipt hanya boleh menghasilkan satu Supplier Invoice serta satu jurnal penerimaan.

**Kontrak transaksi Fase 3:**

1. Goods Receipt valid hanya untuk bisnis aktif, supplier yang sama, produk yang sama-sama tercantum pada PO, dan quantity yang belum melebihi sisa PO.
2. Posting penerimaan menambah stok melalui `StockService`, memperbarui WAC per lokasi pada `inventory_stocks.avg_purchase_cost`, lalu membuat hutang supplier.
3. Supplier Invoice menyimpan total, pembayaran, dan status; `balance_due` dijaga konsisten oleh `SupplierInvoiceService`.
4. Pembayaran supplier tidak boleh melebihi saldo, harus memakai akun pembayaran milik bisnis, dan menghasilkan jurnal Debit AP / Kredit Kas atau Bank.
5. Jurnal penerimaan dan pembayaran idempotent berdasarkan `reference_type` dan `reference_id`.
6. WAC tidak berubah pada pengeluaran; WAC penerimaan dihitung dengan saldo terkunci:

  `WAC Baru = ((Stok Lama × WAC Lama) + (Qty Masuk × Harga Beli)) / (Stok Lama + Qty Masuk)`

### Definition of Done Fase 3

- Goods Receipt dari web dan API membuat Supplier Invoice tepat satu kali.
- Penerimaan menghasilkan jurnal Debit Persediaan / Kredit Hutang Usaha yang seimbang.
- Pembayaran menghasilkan jurnal Debit Hutang Usaha / Kredit Kas atau Bank yang seimbang.
- Pembayaran parsial dan penuh memperbarui `paid_amount`, `balance_due`, dan `status` secara atomic.
- Penerimaan berulang, pembayaran berulang, overpayment, cross-tenant reference, dan over-receipt ditolak.
- WAC per lokasi teruji untuk penerimaan pertama dan penerimaan dengan harga berbeda.
- Route daftar/detail hutang supplier tersedia dan dilindungi business context.
- Test Fase 3 lulus tanpa mematahkan `GoodsReceiptFeatureTest` dan test invoice/POS existing.

#### 3.1 Auto-Journal Goods Receipt → Hutang Usaha
- **File:** `app/Domain/Accounting/AutoJournalService.php`
- **Tambahkan method:** `recordGoodsReceiptJournal(GoodsReceipt $receipt)`
  - Debit: Persediaan Barang (1-1004) = nilai barang masuk
  - Kredit: Hutang Usaha / AP (2-2001) = nilai yang harus dibayar ke supplier
- **Trigger:** Dipanggil dari `GoodsReceiptWebController::store()` setelah stok berhasil ditambahkan
- **Idempotency:** Kembalikan jurnal existing untuk kombinasi `REF_GOODS_RECEIPT` + `goods_receipt_id`.

#### 3.2 Model & Tabel Hutang Supplier
- **Buat migration:** `create_supplier_invoices_table`
  - Kolom: `id`, `business_id`, `supplier_id`, `goods_receipt_id`, `purchase_order_id`, `invoice_number`, `invoice_date`, `due_date`, `total_amount`, `paid_amount`, `balance_due`, `status` (unpaid/partial/paid/void)
- **Buat model:** `app/Models/SupplierInvoice.php`
- **Buat model:** `app/Models/SupplierPayment.php` (tabel: `supplier_payments`)
- **Constraint:** unique `business_id + goods_receipt_id` pada supplier invoice; unique `business_id + payment_number` pada supplier payment; foreign key tenant tetap divalidasi di service.

#### 3.3 Domain Service Hutang Supplier
- **Buat:** `app/Domain/Purchasing/SupplierInvoiceService.php`
  - `createFromGoodsReceipt(GoodsReceipt $receipt): SupplierInvoice`
  - `recordPayment(SupplierInvoice $invoice, array $paymentData): SupplierPayment`
  - Auto-journal saat pembayaran: Debit AP (2-2001), Kredit Kas/Bank (1-1001/1-1002)
- **Orkestrasi:** Service menerima Goods Receipt yang sudah diposting, mengunci receipt/invoice saat posting, dan dipakai oleh web serta API; controller tidak boleh membuat jurnal langsung.

#### 3.4 Controller & Routes
- **Buat:** `app/Http/Controllers/Web/Purchasing/SupplierInvoiceWebController.php`
  - `index()` — daftar hutang supplier dengan aging
  - `show(SupplierInvoice $invoice)` — detail + history pembayaran
  - `recordPayment(Request, SupplierInvoice)` — catat pelunasan hutang
- **Routes:**
  ```
  GET  /purchasing/bills             → purchasing.bills.index
  GET  /purchasing/bills/{bill}      → purchasing.bills.show
  POST /purchasing/bills/{bill}/payments → purchasing.bills.payments.store
  - Tambahkan API endpoint yang ekuivalen bila mobile app memakai alur hutang supplier.
  ```

#### 3.5 UI Halaman Hutang Supplier
- **Buat:** `resources/views/app/purchasing/bills/index.blade.php`
  - Tabel: Supplier, No. Tagihan Vendor, Tanggal, Jatuh Tempo, Total, Terbayar, Sisa, Status
  - Filter by status & aging (< 7 hari, 7–30 hari, > 30 hari)
- **Buat:** `resources/views/app/purchasing/bills/show.blade.php`
  - Detail tagihan + history pembayaran + tombol "Catat Pembayaran"

#### 3.6 Weighted Average Cost (WAC)
- **Migration:** Tambah kolom `avg_purchase_cost` di tabel `inventory_stocks` agar WAC presisi per lokasi.
- **Update `StockService`:** Saat movement goods receipt positif, hitung ulang WAC di dalam row lock; movement keluar hanya memakai WAC/snapshot dan tidak mengubahnya.
- **Validasi:** Tolak cost negatif dan pastikan produk, lokasi, supplier, serta PO berada pada business aktif.
- **Formula:** Setelah stok bertambah, hitung ulang WAC:
  ```
  WAC Baru = (Stok Lama × HPP Lama + Qty Masuk × Harga Beli) / (Stok Lama + Qty Masuk)
  ```

#### 3.7 Feature Test Fase 3
- **Buat:** `tests/Feature/SupplierInvoiceAndPaymentTest.php`
  - Test: Goods Receipt → Supplier Invoice terbuat → Jurnal Hutang terbentuk
  - Test: Catat pembayaran → Saldo hutang berkurang → Jurnal Kas keluar terbentuk
  - Test: WAC produk terupdate setelah menerima barang dengan harga berbeda
  - Test: posting web/API idempotent, overpayment ditolak, duplicate invoice ditolak, over-receipt ditolak, dan tenant isolation dijaga.

### Commands Fase 3
```bash
php artisan migrate
php artisan test tests/Feature/SupplierInvoiceAndPaymentTest.php
php artisan route:list --name=purchasing.bills
```

### ✅ Hasil yang Dicapai Fase 3
- Setiap Goods Receipt otomatis membuat catatan hutang supplier
- Saldo hutang usaha di General Ledger akurat (Debit/Kredit seimbang)
- Pemilik UMKM bisa melihat daftar tagihan vendor yang belum dibayar beserta jatuh tempo
- Pelunasan hutang supplier memicu jurnal kas keluar otomatis
- Harga modal produk (WAC) per lokasi otomatis terupdate saat menerima barang baru
- Goods Receipt dan pembayaran supplier idempotent serta overpayment ditolak

**Verifikasi:** `SupplierInvoiceAndPaymentTest` 3/3 dan `GoodsReceiptFeatureTest` 2/2 lulus setelah fixture snapshot harga diselaraskan dengan schema.

**Catatan lanjutan:** Cash Ledger terpusat, pemetaan akun pembayaran tenant, dan endpoint API khusus daftar hutang tetap menjadi pekerjaan Fase 5 atau perluasan mobile berikutnya.

---

## ✅ FASE 4 — Modul Retur Penjualan & Retur Pembelian (P1) [HARDENED]

**Tujuan:** Barang dikembalikan customer atau ke supplier harus membalik transaksi stok dan keuangan secara tepat.

### Task Rencana Eksekusi

**Keputusan desain:** retur disimpan sebagai dokumen dan item tersendiri; quantity transaksi asli tidak diubah. Fase ini mengimplementasikan retur Invoice dan Goods Receipt secara item-level. POS tetap memakai endpoint refund full-order existing sampai dukungan partial refund memiliki kontrak pembayaran yang lengkap.

**Kontrak transaksi Fase 4:**

1. Hanya Invoice yang sudah dirilis dan Goods Receipt berstatus completed yang dapat diretur.
2. Quantity retur maksimum adalah quantity sumber dikurangi seluruh retur completed sebelumnya.
3. Semua perubahan stok memakai `StockService` dan reference retur; tidak ada mutasi stok langsung dari controller.
4. Penyelesaian retur atomic dan idempotent berdasarkan dokumen retur.
5. Retur penjualan mengembalikan stok dan membalik pendapatan/piutang atau kas sesuai sumber Invoice.
6. Retur pembelian mengurangi stok dan saldo hutang supplier; bila saldo hutang tidak cukup, retur ditolak.

### Definition of Done Fase 4

- Sales Return dan Purchase Return memiliki migration, model, item, service, controller, route, dan UI web.
- Return item tidak dapat melebihi quantity yang masih dapat diretur.
- Completion kedua jenis retur menghasilkan stock movement dan jurnal double-entry seimbang.
- Duplicate completion tidak menggandakan stok atau jurnal.
- Cross-tenant source document dan return document ditolak.
- Return terhadap draft/void/uncompleted source ditolak.
- Test mencakup partial return, full return, over-return, idempotency, dan saldo supplier.

#### 4.1 Retur Penjualan (Sales Return)
- **Migration:** `create_sales_returns_table`
  - Kolom: `id`, `business_id`, `invoice_id`, `pos_order_id`, `customer_id`, `location_id`, `return_number`, `return_date`, `reason`, `status` (draft/approved/completed/void), `total_amount`, `refund_method` (credit_note/cash_refund/store_credit)
- **Migration:** `create_sales_return_items_table`
  - Kolom: `sales_return_id`, `product_id`, `item_name`, `quantity`, `unit_price`, `unit_hpp`, `subtotal`
- **Model:** `SalesReturn`, `SalesReturnItem`
- **Service:** `app/Domain/Commerce/SalesReturnService.php`
  - `createFromInvoice(Invoice $invoice, array $items): SalesReturn`
  - `approve(SalesReturn $return): SalesReturn`
  - `complete(SalesReturn $return): SalesReturn` → `StockService::restoreForInvoiceReturn()` + jurnal reversal
- **Jurnal Retur Penjualan:**
  - Kredit: Piutang / Kas (membalik penjualan)
  - Debit: Retur Penjualan (4-4002)
  - Kredit: HPP / COGS (HPP dibatalkan)
  - Debit: Persediaan (barang masuk kembali ke stok)
- **Controller:** `SalesReturnWebController`
  - Routes: `GET /sales/returns`, `POST /sales/returns`, `POST /sales/returns/{id}/approve`, `POST /sales/returns/{id}/complete`
- **UI:** Index daftar retur, Create (pilih invoice/POS sumber, pilih item, alasan, metode refund), Show detail
- **Scope implementasi:** Invoice menjadi sumber utama pada Fase 4; POS full refund existing tidak diubah menjadi partial sebelum payment/refund ledger tersedia.

#### 4.2 Retur Pembelian (Purchase Return)
- **Migration:** `create_purchase_returns_table`
  - Kolom: `id`, `business_id`, `goods_receipt_id`, `supplier_id`, `location_id`, `return_number`, `return_date`, `reason`, `status`, `total_amount`, `debit_note_number`
- **Migration:** `create_purchase_return_items_table`
- **Service:** `app/Domain/Purchasing/PurchaseReturnService.php`
  - `createFromGoodsReceipt(GoodsReceipt $receipt, array $items): PurchaseReturn`
  - `complete(PurchaseReturn $return): PurchaseReturn` → `StockService::recordMovement(TYPE_GOODS_ISSUE)` + potong hutang supplier
- **Jurnal Retur Pembelian:**
  - Kredit: Persediaan (stok dikurangi)
  - Debit: Hutang Usaha AP (hutang berkurang karena barang dikembalikan)
- **Controller + UI:** Mirip Sales Return, berbasis referensi Goods Receipt
- **Referensi item:** Return item wajib menyimpan `goods_receipt_item_id` agar validasi quantity dan cost tidak bergantung pada agregat SupplierInvoice header.

#### 4.3 Feature Test Fase 4
- Test: Invoice confirmed → Retur Penjualan → Stok masuk kembali → Jurnal reversal terbentuk
- Test: Goods Receipt → Retur Pembelian → Stok berkurang → Hutang supplier berkurang
- Test: Retur tidak bisa dibuat melebihi quantity original transaksi
- Test: completion berulang idempotent dan akses lintas bisnis ditolak

### Commands Fase 4
```bash
php artisan migrate
php artisan test tests/Feature/SalesReturnIntegrationTest.php
php artisan test tests/Feature/PurchaseReturnIntegrationTest.php
```

### ✅ Hasil yang Dicapai Fase 4
- Pemilik UMKM bisa memproses pengembalian barang dari pelanggan dengan referensi Invoice/POS
- Stok otomatis bertambah kembali saat retur penjualan selesai
- Jurnal akuntansi retur terbentuk otomatis (membalik HPP dan Pendapatan)
- Retur ke supplier otomatis mengurangi hutang usaha ke supplier tersebut
- Tidak bisa retur lebih dari quantity yang dibeli/dijual
- Completion retur idempotent dan akses tenant dijaga oleh business scope/controller checks

**Verifikasi:** `SalesAndPurchaseReturnIntegrationTest` 2/2 dan regression set Fase 3 (`SupplierInvoiceAndPaymentTest`, `GoodsReceiptFeatureTest`) total 7/7 lulus. Migration `2026_09_04_000070_create_sales_and_purchase_returns_tables` dan 12 route retur tervalidasi.

**Catatan scope:** Invoice, Goods Receipt, POS partial/full refund, serta endpoint API retur kini tersedia. Ledger kas terpusat dan refund payment allocation detail tetap menjadi pekerjaan Fase 5.

### Fase 4B — Partial POS Refund & API Return [SELESAI]

**Status:** SELESAI. Fase 4B menyelesaikan dua gap yang sebelumnya ditunda.

- Partial POS refund memakai item-level quantity, validasi sisa quantity, row lock, dan status `partial_refund`/`refunded`.
- Refund POS membuat jurnal reversal HPP/pendapatan dan tidak boleh diproses ulang.
- API menyediakan endpoint create/approve/complete untuk Sales Return dan Purchase Return dengan business scope yang sama seperti web.
- API mengembalikan error validasi domain secara konsisten dan seluruh operasi memakai service domain yang sama.
- Test mencakup partial/full POS refund, repeated refund, over-refund, API tenant isolation, dan API idempotency.

**Tambahan hardening:** Goods Receipt web/API sekarang mengunci PO, menolak produk di luar PO dan over-receipt kumulatif, serta membedakan status PO partial dan completed. Endpoint API Goods Receipt `index/show` juga sudah diimplementasikan.

**Verifikasi lanjutan:** regression suite Fase 1-4 terarah lulus **16/16 test**.

---

## ✅ FASE 5 — Buku Kas, Bank & Arus Kas Terpusat (P1) [SELESAI]

**Tujuan:** Saldo kas dan bank akurat, semua mutasi uang bisa dilacak dalam satu buku.

### Task Rencana Eksekusi

**Keputusan desain:** `payment_accounts` tetap dipakai untuk rekening pembayaran platform/SaaS. Buku kas tenant memakai `cash_accounts` sebagai master akun kas/bank dan `cash_transactions` sebagai ledger immutable. Saldo akun adalah cache yang hanya boleh berubah melalui `CashLedgerService` dalam transaksi dan row lock.

**Kontrak transaksi Fase 5:**

1. Setiap akun kas tenant memiliki `business_id`, jenis akun, saldo berjalan, dan mapping COA.
2. Setiap mutasi memiliki reference transaksi sumber, tanggal, nominal positif, dan tipe `in/out/transfer`.
3. Inflow menambah saldo; outflow mengurangi saldo dan menolak saldo minus.
4. Transfer mengunci dua akun secara deterministik dan membuat dua ledger rows dalam satu transaksi.
5. Semua alur pembayaran existing memakai ledger yang sama dan tidak boleh menggandakan mutasi pada retry.
6. Query saldo dan mutasi selalu tenant-scoped.

### Definition of Done Fase 5

- Cash account tenant dan ledger migration/model/service tersedia.
- Saldo atomic dan pencegahan saldo minus teruji.
- Transfer antar akun atomic dan tidak kehilangan nominal.
- Invoice payment, supplier payment, dan expense otomatis masuk ledger.
- Duplicate reference ditolak/idempotent.
- Halaman kas & bank menampilkan saldo dan mutasi terbaru.
- Test Fase 5 dan regression Fase 1-4 lulus.

#### 5.1 Akun Kas Tenant
- **Migration:** `cash_accounts` menyimpan `business_id`, `current_balance`, jenis akun, dan mapping COA; `payment_accounts` platform tidak dipakai sebagai ledger tenant.
- **Prinsip:** Setiap transaksi kas wajib update `current_balance` secara atomic

#### 5.2 Buku Kas Harian
- **Migration:** `create_cash_transactions_table`
  - Kolom: `id`, `business_id`, `account_id`, `type` (in/out/transfer), `amount`, `reference_type`, `reference_id`, `description`, `transaction_date`, `created_by`
- **Model:** `CashTransaction`
- **Service:** `app/Domain/Finance/CashLedgerService.php`
  - `recordInflow(account, amount, reference, description)`
  - `recordOutflow(account, amount, reference, description)`
  - `transfer(from, to, amount)` — update dua saldo sekaligus dalam 1 transaction

#### 5.3 Integrasi ke Transaksi Existing
- **Invoice Payment:** Saat `recordPayment()` → `CashLedgerService::recordInflow()`
- **Supplier Payment:** Saat bayar hutang → `CashLedgerService::recordOutflow()`
- **Expense:** Saat catat pengeluaran → `CashLedgerService::recordOutflow()`

#### 5.4 UI Buku Kas & Bank
- **Buat:** `resources/views/app/finance/cash-bank/index.blade.php`
  - Kartu saldo per akun (Kas Utama, BRI, BCA, dll)
  - Tabel mutasi terbaru
  - Tombol: Catat Pemasukan, Catat Pengeluaran, Transfer Antar Rekening
- **Buat:** `resources/views/app/finance/cash-bank/ledger.blade.php`
  - Buku besar per akun dengan filter tanggal

#### 5.5 AR/AP Aging Dashboard
- **Buat view:** `resources/views/app/finance/receivables.blade.php`
  - Grouping: < 7 hari (hijau), 7–30 hari (kuning), 31–60 hari (oranye), > 60 hari (merah)
- **Buat view:** `resources/views/app/finance/payables.blade.php`

### ✅ Hasil yang Dicapai Fase 5
- Pemilik UMKM tahu saldo kas dan bank real-time
- Semua uang masuk/keluar tercatat dengan referensi transaksi sumbernya
- Transfer antar rekening tidak menghilangkan uang (double update saldo)
- Daftar piutang jatuh tempo tersaji dengan aging yang jelas

**Verifikasi:** `CashLedgerIntegrationTest` 2/2 dan regression Fase 1-4 total **18/18 test lulus**. Migration `2026_09_04_000080_create_cash_accounts_and_transactions_tables` berhasil, route dashboard/ledger/transfer/AR/AP tersedia.

**Catatan scope:** Cash Ledger API dan API hutang supplier untuk mobile sudah tersedia. Rekonsiliasi settlement gateway tetap menjadi pekerjaan lanjutan.

### Fase 5B — Rekonsiliasi Settlement Gateway [SELESAI]

**Status:** SELESAI.

- Settlement wajib menyimpan allocation ke pembayaran sumber, bukan hanya total agregat.
- Gross settlement harus sama dengan total allocation yang dicocokkan; fee dan net wajib memenuhi `net = gross - fee`.
- Pembayaran hanya boleh dicocokkan sekali dan harus tenant-scoped.
- Rekonsiliasi atomic: allocation, status settlement, Cash Ledger net inflow, dan jurnal fee dibuat dalam satu transaksi.
- Retry settlement yang sama mengembalikan hasil existing tanpa menggandakan saldo atau jurnal.
- Selisih dan pembayaran yang tidak ditemukan ditolak dengan response 422.

**Endpoint API:** `GET/POST /api/v1/finance/settlements`, `POST /api/v1/finance/settlements/reconcile`.

**Verifikasi:** `PaymentSettlementReconciliationTest` 2/2 lulus. Allocation pembayaran, Cash Ledger net inflow, jurnal fee, dan retry idempotent sudah tervalidasi.

---

## ✅ FASE 6 — Restrukturisasi Sidebar 10 Pilar UMKM & Role-Based Permissions (P1) [SELESAI]

**Tujuan:** Navigasi sidebar mencerminkan 10 pilar alur kerja alami pemilik UMKM dengan perlindungan hak akses (RBAC) per menu item untuk menjamin keamanan informasi bisnis (misal: Kasir tidak melihat HPP/margin modal dan Keuangan; Gudang fokus ke stok & logistik; Finance fokus ke buku kas & laporan).

### Task Rencana Eksekusi

**Keputusan Desain:**
1. Navigasi dikelompokkan ke dalam 10 Pilar Terstruktur:
   - **🏠 Dashboard & AI Assistant** (Ringkasan performa harian & asisten cerdas)
   - **💼 Bisnis** (Profil usaha, cabang, dan gudang)
   - **🛍️ Penjualan** (Terminal Kasir POS, Invoice & Piutang, Riwayat Transaksi & Shift, Pelanggan CRM, Pesanan/Penawaran, Retur Penjualan)
   - **📦 Pembelian** (Supplier, Purchase Order, Tagihan & Hutang Vendor, Retur Pembelian)
   - **📦 Persediaan** (Katalog Produk, Bahan Baku & Resep, Stok Real-Time, Mutasi Stok/Kartu Stok, Transfer Gudang, Stock Opname)
   - **🧮 HPP & Produksi** (Kalkulator HPP 3-Pilar, Upah Kerja & Mesin, BEP & Profitabilitas, Simulasi What-If)
   - **💰 Keuangan** (Kas & Bank, Buku Kas Harian / Ledger, Beban Operasional, Piutang Usaha AR, Hutang Usaha AP, Jurnal Akuntansi Otomatis)
   - **📊 Laporan** (Laporan & Analitik Bisnis, Laporan Kasir POS)
   - **🤖 AI Assistant** (Asisten cerdas analitik & aksi cepat)
   - **⚙️ Pengaturan & SaaS** (Pengaturan Toko, Kontrol Akses Role, Paket Langganan & Kuota, Komunitas Owner, Dukungan Produk)
2. **Permission Guarding Terpadu:**
   - Setiap grup menu hanya ditampilkan jika pengguna memiliki izin untuk minimal satu sub-menu di dalamnya.
   - Setiap link menu divalidasi dengan `\App\Support\Context::hasPermission(...)` dan `\App\Support\Context::isOwner()`.
   - Menjaga seluruh identifier tour (`tour-nav-dashboard`, `tour-nav-calculator`, `tour-nav-products`, `tour-nav-materials`, `tour-nav-labor-machines`, `tour-nav-simulator`, `tour-nav-profitability`, `tour-nav-reports`, `tour-nav-settings`) agar tur interaktif onboarding tetap bekerja 100%.
3. **UX Interaktif Alpine.js:**
   - Accordion grup otomatis terbuka jika rute aktif saat ini berada di dalam grup tersebut (`salesOpen`, `purchasingOpen`, `inventoryOpen`, `costingOpen`, `financeOpen`, `reportsOpen`, `settingsOpen`).

### Definition of Done Fase 6:
- [x] Restrukturisasi navigasi sidebar `resources/views/layouts/app.blade.php` sesuai 10 pilar UMKM.
- [x] Integrasi link Retur Penjualan (`sales.returns.index`), Retur Pembelian (`purchase.returns.index`), Tagihan Vendor (`purchasing.bills.index`), Kas & Bank (`finance.cash-bank.index`), Buku Kas Ledger (`finance.cash-bank.ledger`), Piutang AR (`finance.receivables`), dan Hutang AP (`finance.payables`).
- [x] Permission guard aktif pada level grup dan level item (Kasir tidak melihat margin HPP / Keuangan; Gudang tidak melihat POS / Keuangan).
- [x] Tour IDs tetap utuh dan kompatibel dengan guided onboarding tour.
- [x] Test suite `LayoutSidebarNavbarPlanTest.php` diperluas dan lulus 100% untuk semua peran (Owner, Cashier, Warehouse, Finance).

#### 6.1 Audit & Relokasi Menu ke 10 Pilar
- File: `resources/views/layouts/app.blade.php`
- Relokasi Upah Kerja & Mesin, What-If Simulator, dan BEP ke dalam grup **HPP & Produksi**.
- Integrasikan sub-menu Retur Penjualan di **Penjualan** dan Retur Pembelian serta Tagihan Vendor di **Pembelian**.
- Tambahkan sub-menu Kas & Bank, Ledger, Piutang AR Aging, dan Hutang AP Aging di **Keuangan**.

#### 6.2 Permission Guards per Menu Item
- Implementasi kondisi `@if(\App\Support\Context::hasPermission('...'))` pada masing-masing item.
- Sembunyikan collapsible container jika seluruh child item tidak dapat diakses oleh user aktif.

#### 6.3 Automated Feature Test
- Update `tests/Feature/LayoutSidebarNavbarPlanTest.php` untuk memverifikasi rendering navigasi 10 pilar bagi Owner dan isolasi menu bagi role Kasir, Gudang, dan Finance.

### Commands Fase 6
```bash
php artisan test tests/Feature/LayoutSidebarNavbarPlanTest.php
```

### ✅ Hasil yang Dicapai Fase 6
- Sidebar navigasi telah direstrukturisasi secara komprehensif mengikuti 10 pilar bisnis UMKM Cooca.
- Seluruh modul Fase 1 s.d. 5 (Retur Penjualan, Retur Pembelian, Tagihan Supplier, Kas & Bank, Ledger, AR/AP Aging) terintegrasi langsung di sidebar.
- Hak akses berbasis peran (RBAC) diterapkan rapi: Kasir, Staf Gudang, dan Staf Keuangan hanya melihat menu yang menjadi tanggung jawabnya.
- Semua tour identifiers onboarding tetap terjaga dan test suite lulus 100%.

---

---

## ✅ FASE 7 — Engine Pelaporan Berbasis Snapshot (P2) [SELESAI]

**Tujuan:** Laporan Laba Rugi, Arus Kas, AR/AP Aging, dan Valuasi Stok berbasis data transaksi aktual dengan ekspor CSV/Excel dan filter tanggal fleksibel.

### Sub-task Detail & Hasil

| Sub-task | File Target | Hasil yang Dicapai |
|----------|-------------|-------------------|
| 7.1 Domain Reporting Engine | `app/Domain/Report/FinancialReportService.php` | Service terisolasi untuk agregasi Laba Rugi (POS + Invoice - Retur - COGS - Beban), Arus Kas (Inflow vs Outflow), AR/AP Aging (5 bucket), dan Valuasi Stok (WAC & turnover) |
| 7.2 Web Controller & Export Stream | `app/Http/Controllers/Web/ReportWebController.php` | Controller multi-tab dengan filter preset (Hari ini, 7 hari, Bulan ini, Tahun ini, Custom) & Export CSV streaming dengan UTF-8 BOM |
| 7.3 UI Reporting Suite Dashboard | `resources/views/app/reports/index.blade.php` | Dashboard 5 tab (Laba Rugi, Arus Kas, Umur Piutang/Hutang, Valuasi Stok, Struktur HPP) dengan KPI cards, visual progress, dan print-ready styling |
| 7.4 Feature Test Suite | `tests/Feature/ComprehensiveFinancialReportingTest.php` | **5/5 tests passed** (26 assertions) memvalidasi akurasi perhitungan matematika dan ekspor file |

### Commands Fase 7
```bash
php artisan test tests/Feature/ComprehensiveFinancialReportingTest.php
```

### ✅ Hasil yang Dicapai Fase 7
- **Laba Rugi Riil (Income Statement)**: Menggabungkan omzet POS Kasir, Faktur Penjualan B2B, dikurangi Retur Penjualan, HPP aktual produk, dan Beban Operasional (`expenses`). Menghitung Laba Kotor, Laba Bersih, dan Margin Laba secara akurat.
- **Laporan Arus Kas (Cash Flow)**: Melacak uang riil masuk (POS tunai/transfer, pelunasan piutang invoice, kas masuk lain) dan keluar (pelunasan tagihan supplier, beban operasional, refund retur kas, kas keluar lain) beserta Arus Kas Bersih (*Net Cash Flow*).
- **Laporan Umur Piutang (AR Aging) & Hutang (AP Aging)**: Tagihan belum lunas dikelompokkan ke 5 kategori jatuh tempo: *Current/Belum Jatuh Tempo, 1-30 Hari, 31-60 Hari, 61-90 Hari, dan >90 Hari (Macet)* dengan badge visual peringatan.
- **Laporan Valuasi & Perputaran Stok**: Menghitung nilai total aset stok gudang berdasarkan *Weighted Average Cost (WAC)*, serta mengklasifikasikan produk menjadi *Fast Moving* (> threshold dalam 30 hari), *Slow Moving*, dan *Dead Stock / Tidak Bergerak* (>90 hari tanpa mutasi).
- **Ekspor CSV/Excel**: Tersedia tombol download CSV per masing-masing tab laporan dengan *UTF-8 BOM* yang rapi saat dibuka di Microsoft Excel.

---

## ⏳ FASE 8 — AI Assistant Guardrails & Analytics (P3)

**Tujuan:** AI memberikan insight bisnis yang relevan tanpa bisa mengeksekusi aksi destruktif.

### Sub-task Detail

#### 8.1 Tool Registry Berbasis Role
- Kasir: hanya query produk & harga
- Owner: analisis margin, prediksi stok, insight supplier

#### 8.2 Anomaly Detector
- Margin turun > 10% → notifikasi
- Harga beli naik > 15% dari WAC → alert
- Stok fast-moving < safety stock → warning
- Invoice jatuh tempo > 60 hari → reminder

#### 8.3 Rekomendasi Restock
- Formula: Rata-rata penjualan harian × lead time supplier = qty reorder
- Tombol "Buat PO Restock" langsung dari notifikasi

### ✅ Hasil yang Diharapkan Fase 8
- AI tidak bisa menghapus atau mengubah transaksi
- Pemilik mendapat peringatan dini jika margin drop
- Sistem merekomendasikan kapan dan berapa harus reorder

---

## 📊 Ringkasan Semua Fase

| Fase | Status | Prioritas | Hasil Kunci |
|------|--------|-----------|-------------|
| 1 — Fondasi Inventori | ✅ DONE | P0 | StockService atomic, stok minus dicegah |
| 2 — Invoice → Stok & Jurnal | ✅ DONE | P0 | Invoice memotong stok + double-entry journal |
| 3 — Hutang Supplier & WAC | ✅ DONE | P1 | Goods Receipt = hutang tercatat + WAC terupdate |
| 4 — Modul Retur | ✅ DONE | P1 | Retur jual/beli membalik stok & keuangan |
| 5 — Buku Kas & Bank | ✅ DONE | P1 | Saldo kas real-time + AR/AP Aging |
| 6 — Sidebar UMKM & UI Alignment | ✅ DONE | P1 | Navigasi 10 pilar UMKM, RBAC Guards, dan Polishing UI |
| 7 — Engine Pelaporan | ✅ DONE | P2 | Laporan Laba Rugi riil, Arus Kas, Aging AR/AP, Valuasi Stok, & Ekspor CSV |
| 8 — AI Guardrails | ⏳ Next | P3 | AI aman + anomaly detector |

> **Untuk melanjutkan fase berikutnya:** Ketik `"task rencana perbaikan fase 8 KEMUDIAN EKSEKUSI FASE 8"`
