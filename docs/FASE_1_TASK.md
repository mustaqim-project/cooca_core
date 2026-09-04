# Task List: Fase 1 — Fondasi Inventori & Stock Service Centralization (P0)

Status: COMPLETED ✅ (100% Selesai & Terverifikasi)

## 1.1 Atomic Lock & Single Gateway StockService
- [x] 1.1.1 Buat exception class khusus domain inventori: `App\Domain\Inventory\Exceptions\InsufficientStockException`
- [x] 1.1.2 Tambahkan opsi konfigurasi `allow_negative_stock` di tabel `businesses` (migration aman nullable default false)
- [x] 1.1.3 Perbarui `StockService::recordMovement` dengan pessimistic locking (`lockForUpdate()`) di dalam DB::transaction
- [x] 1.1.4 Implementasikan validasi pencegahan stok minus di `StockService` jika `allow_negative_stock == false`
- [x] 1.1.5 Tambahkan method sentral `deductForInvoiceSale(...)` dan `restoreForInvoiceReturn(...)` di `StockService` sebagai gateway resmi penjualan Invoice dan Retur

## 1.2 Proteksi Transaksi Komersial (Anti Hard-Delete)
- [x] 1.2.1 Update `PurchaseOrderWebController::destroy` — Tolak penghapusan jika status bukan `draft` (arahkan ke status `cancelled`)
- [x] 1.2.2 Update `InvoiceWebController::destroy` — Tolak penghapusan jika status bukan `draft` (arahkan ke status `void`/`cancelled`)
- [x] 1.2.3 Periksa assertion multi-tenant ganda (`business_id === Context::businessId()`) pada seluruh aksi destruktif transaksi

## 1.3 Verifikasi & Regression Test
- [x] 1.3.1 Jalankan syntax check dan linting pada seluruh file PHP yang diperbarui (Passed 100%)
- [x] 1.3.2 Buat dan jalankan automated unit test (`tests/Unit/StockServiceConcurrencyAndIntegrityTest.php`) (Passed: 1 test, 5 assertions)
- [x] 1.3.3 Update checklist task Fase 1 menjadi selesai (Completed)
