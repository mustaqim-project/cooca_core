# Task List: Fase 2 — Integrasi Sales Invoice ke Inventori & Keuangan (P0)

Status: **COMPLETED ✅** — 2026-09-03

---

## 2.1 State Machine Invoice (Draft → Released / Confirmed → Paid / Void)
- [x] 2.1.1 Tambahkan kolom `location_id` (nullable foreignUuid ke `locations`) pada tabel `invoices` — Migration `2026_09_03_000002_add_location_id_to_invoices_table.php` dijalankan
- [x] 2.1.2 Perbarui model `Invoice`: tambahkan `location_id` di `$fillable` dan relasi `location(): BelongsTo`
- [x] 2.1.3 Tambahkan method transisi state di `InvoiceService`:
  - `confirmAndRelease(Invoice $invoice, ?string $locationId)` — Mengubah status draft → unpaid, memotong stok, dan mencatat jurnal
  - `voidInvoice(Invoice $invoice, ?string $reason)` — Membatalkan invoice dan mengembalikan stok

## 2.2 Otomasi Pemotongan Stok dari Invoice Released / Paid
- [x] 2.2.1 Hubungkan `InvoiceService::confirmAndRelease` dengan `StockService::deductForInvoiceSale` via `processStockAndJournalForReleasedInvoice`
- [x] 2.2.2 Jika invoice langsung dibuat dengan status bukan draft, pemotongan stok otomatis terpicu secara atomic (lihat kondisi di `createFromProducts`)
- [x] 2.2.3 Saat invoice di-void, stok dikembalikan via `StockService::restoreForInvoiceReturn`

## 2.3 Auto-Journal Piutang & Penerimaan Kas Invoice
- [x] 2.3.1 Method `recordInvoiceIssuedJournal(Invoice $invoice)` ditambahkan pada `AutoJournalService`:
  - Debit: Piutang Pelanggan (1-1003)
  - Kredit: Pendapatan Penjualan (4-4001)
  - Kredit: Hutang PPN Keluaran (2-2002) jika ada pajak
  - Debit: Beban Pokok Penjualan / HPP (5-5001)
  - Kredit: Persediaan Barang Dagang (1-1004)
- [x] 2.3.2 Method `recordInvoicePaymentJournal(InvoicePayment $payment)` ditambahkan pada `AutoJournalService`:
  - Debit: Kas (1-1001) atau Bank (1-1002)
  - Kredit: Piutang Pelanggan (1-1003)
- [x] 2.3.3 Auto-journal dipanggil saat invoice disahkan dan saat pembayaran dicatat di `InvoiceService::recordPayment`

## 2.4 Tampilan & Aksi UI
- [x] 2.4.1 Dropdown pemilihan Gudang/Outlet asal barang ditambahkan ke form buat invoice (`invoices/create.blade.php`) — tampil hanya jika ada data lokasi, default gudang utama (`is_primary`)
- [x] 2.4.2 Tombol aksi di halaman detail invoice (`invoices/show.blade.php`):
  - **"Konfirmasi & Rilis Faktur"** — tampil saat status `draft`, memicu `POST /invoices/{id}/confirm` + konfirmasi dialog
  - **"Void / Batalkan"** — tampil saat status bukan draft/void dan `paid_amount == 0`, memicu `POST /invoices/{id}/void` + konfirmasi dialog
  - Badge **nama gudang asal** ditampilkan di bawah info pihak (penjual/pembeli) jika `location_id` terisi

## 2.5 Route & Controller
- [x] 2.5.R Rute `POST /invoices/{invoice}/confirm` dan `POST /invoices/{invoice}/void` terdaftar di `routes/web.php`
- [x] 2.5.C Method `confirm(Request, Invoice)` dan `void(Request, Invoice)` ditambahkan ke `InvoiceWebController`

## 2.6 Verifikasi & Automated Test
- [x] 2.6.1 Syntax dan logika file terkait telah diperiksa
- [x] 2.6.2 Feature test `tests/Feature/InvoiceStockAndJournalIntegrationTest.php` dibuat dan dijalankan:
  - **6/6 tests passed** ✅ (14 assertions)
  - Test 1: Draft invoice tidak memotong stok
  - Test 2: Konfirmasi invoice memotong stok dari gudang yang dipilih
  - Test 3: Konfirmasi invoice menghasilkan jurnal debit/kredit (Piutang & Pendapatan)
  - Test 4: Void invoice mengembalikan stok ke gudang
  - Test 5: Invoice non-draft langsung memotong stok saat dibuat
  - Test 6: InsufficientStockException dilempar jika stok tidak cukup

---

## Ringkasan File yang Dimodifikasi / Dibuat

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_09_03_000002_add_location_id_to_invoices_table.php` | [BARU] Migration kolom `location_id` di `invoices` |
| `app/Models/Invoice.php` | Tambah `location_id` di `$fillable` + relasi `location()` |
| `app/Models/JournalEntry.php` | Tambah konstanta `REF_INVOICE` dan `REF_INVOICE_PAYMENT` |
| `app/Models/StockMovement.php` | Tambah konstanta `TYPE_INVOICE_SALE` dan `TYPE_INVOICE_RETURN` |
| `app/Domain/Inventory/StockService.php` | Tambah `deductForInvoiceSale()` dan `restoreForInvoiceReturn()` |
| `app/Domain/Accounting/AutoJournalService.php` | Tambah `recordInvoiceIssuedJournal()` dan `recordInvoicePaymentJournal()` |
| `app/Domain/Commerce/InvoiceService.php` | Tambah `confirmAndRelease()`, `voidInvoice()`, dan integrasi stok+jurnal |
| `app/Http/Controllers/Web/InvoiceWebController.php` | Tambah method `confirm()`, `void()`; kirim `$locations` ke view create |
| `routes/web.php` | Daftarkan rute `POST invoices/{id}/confirm` dan `invoices/{id}/void` |
| `resources/views/app/invoices/create.blade.php` | Tambah dropdown pilihan gudang asal barang |
| `resources/views/app/invoices/show.blade.php` | Tambah tombol Konfirmasi, Void, dan badge lokasi gudang |
| `tests/Feature/InvoiceStockAndJournalIntegrationTest.php` | [BARU] 6 integration tests — semua passed |
