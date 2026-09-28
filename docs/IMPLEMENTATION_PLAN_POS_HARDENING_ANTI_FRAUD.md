# Rencana Implementasi Bertahap: Hardening Keamanan Siber, Proteksi Fraud Internal, Standar Modal Pop-Up XXL Bento Apple HIG & Integrasi Public QR Order pada Modul POS

> **Dokumen Rencana:** `PLAN-17-POS-HARDENING-ANTI-FRAUD`  
> **Rujukan PRD:** [`docs/prd/PRD-17-POS-TERMINAL-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-17-POS-TERMINAL-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md)  
> **Direktif Acuan:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md) & [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md)  
> **Status:** COMPLETED ✅ (All 5 Phases Executed, Verified & Documented)

---

## 1. Ikhtisar Eksekusi Bertahap (Phased Roadmap)

Rencana perbaikan ini dirancang untuk mengeksekusi penguatan menyeluruh terhadap modul **Point of Sale (`resources/views/app/pos`)**, modul publik pemesanan meja **QR Table Ordering (`resources/views/public/qr-order`)**, serta seluruh controller, domain service, dan komponen UI terkait. Perbaikan dibagi menjadi 5 fase terstruktur:

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│ FASE 1: Keamanan Siber, Proteksi SSRF Printer LAN & Eliminasi Default PIN [P1 - Kritis] │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 2: Proteksi Fraud Kasir: Strict Blind Cash Count & Anti Price Tampering [P1]       │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 3: Standar Modal Pop-Up XXL Bento Apple HIG v2.0 & Eliminasi Alert Native [P2]     │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 4: Hardening Public QR Order & Penegakan Context-Aware UI 20 Sektor [P2]           │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│ FASE 5: Pengujian Otomatis Komprehensif, Hardening & Pembaruan Dokumentasi [P1]         │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Rincian Pekerjaan Per Fase

### FASE 1: Keamanan Siber, Proteksi SSRF Printer LAN & Eliminasi Default PIN (P1 - Kritis) ✅ `[COMPLETED]`

**Tujuan:** Menutup celah eksploitasi SSRF pada printer jaringan dan mengamankan otorisasi Supervisor PIN.

1. **Proteksi SSRF pada `PosPrinterWebController` & `NetworkConnector`:**
   - Menambahkan validasi sanitizer IP pada `PosPrinterWebController@store` dan `@update` yang memblokir alamat IP privat berbahaya (`127.0.0.0/8`, `169.254.169.254`, `0.0.0.0`, `::1`, `localhost`).
   - Membatasi port printer LAN hanya pada port printer standar (9100, 515, 631).
   - Membatasi timeout koneksi socket TCP maksimal 2.0 detik.
2. **Eliminasi Fallback Insecure Default PIN `'1234'`:**
   - Menghapus fallback `'1234'` pada `PosOrderWebController@verifySupervisorAuthorization` dan `PosPrinterWebController@manualDrawerPop`.
   - Jika PIN Supervisor belum diset oleh Owner, sistem secara tegas menolak tindakan berisiko dan mengembalikan pesan informatif untuk mengatur PIN di Profil/Pengaturan Toko.
   - Memastikan PIN tersimpan dalam format ter-hash Bcrypt dan diproteksi dari pengembalian plaintext ke frontend.

---

### FASE 2: Proteksi Fraud Kasir: Strict Blind Cash Count & Anti Price Tampering (P1 - Kritis) ✅ `[COMPLETED]`

**Tujuan:** Menutup celah penggelapan kas laci (*cash skimming*) dan manipulasi harga sisi klien.

1. **Penegakan Strict Blind Cash Count (`shifts.blade.php`, `terminal.blade.php`, & `PosShiftWebController.php`):**
   - Menyembunyikan elemen `Total Harapan di Laci (Sistem)` dan live variance calculation dari antarmuka penutupan shift kasir standar di frontend dan API backend.
   - Menghapus prefill `this.shiftActualCash = data.summary.expected_cash` pada Alpine.js `terminal.blade.php`.
   - Mengubah modal penutupan shift kasir murni menjadi form input fisik riil (lembar/koin).
   - Menambahkan proteksi otorisasi peran: hanya peran `owner` atau `supervisor` yang dapat melihat total harapan sistem saat tutup shift.
2. **Validasi Harga Sisi Server (Server-Authoritative Pricing):**
   - Memperbaiki `PosOrderService@checkout` agar memverifikasi ulang harga produk katalog terhadap database master (`Product::selling_price` & `ProductChannelPrice`), mengabaikan `unit_price` dari request browser klien.
   - Mencegah manipulasi harga lewat modifikasi script JavaScript atau payload HTTP.
3. **Endpoint Asinkron Validasi Voucher (`/pos/validate-voucher`):**
   - Membuat endpoint AJAX `POST /pos/validate-voucher` di `PosTerminalWebController`.
   - Mengganti kalkulasi mock client-side (`Math.min(subtotal * 0.1, 50000)`) dengan pemanggilan API riil yang memeriksa kode voucher, syarat minimal belanja, tanggal berlaku, dan sisa kuota.

---

### FASE 3: Standar Modal Pop-Up XXL Bento Apple HIG v2.0 & Eliminasi Alert Native (P2 - Sedang) ✅ `[COMPLETED]`

**Tujuan:** Menegakkan modal pop-up berukuran paling besar dan responsif untuk berbagai perangkat (XXL Bento Dialog di Desktop, Centered Responsive di Tablet, Bottom Sheet penuh di Mobile) serta mengeliminasi dialog browser native.

1. **Restrukturisasi Seluruh Modal ke Standar XXL 2-Kolom Lapang:**
   - **`shifts.blade.php`:**
     - Modal Buka Shift: Dari `max-w-lg` diubah menjadi `max-w-5xl xl:max-w-6xl` (2 kolom: Kiri = Form Modal & Denominasi Pecahan Uang, Kanan = Ringkasan Register & Kasir Aktif).
     - Modal Tutup Shift: Dari `max-w-lg` diubah menjadi `max-w-5xl xl:max-w-6xl` (2 kolom: Kiri = Input Fisik Pecahan Laci, Kanan = Info Sesi Kasir & Catatan).
     - Modal Mutasi Kas: Dari `max-w-md` diubah menjadi `max-w-3xl lg:max-w-4xl`.
   - **`printers/index.blade.php`:**
     - Modal Tambah/Edit Printer: Dari `max-w-xl` diubah menjadi `max-w-5xl xl:max-w-6xl` (2 kolom: Kiri = Koneksi & IP Interface, Kanan = Pratinjau Kategori Menu & Kemampuan Hardware).
     - Modal Test Drawer Pop: Dari `max-w-md` diubah menjadi `max-w-2xl`.
   - **`tables.blade.php`:**
     - Modal Form Meja: Dari `max-w-sm` diubah menjadi `max-w-3xl lg:max-w-4xl`.
   - **`terminal.blade.php`:**
     - Modal Detail Item, SPK Kendaraan, & Resep Dapur diperluas ke standar Apple HIG XXL.
2. **Refactoring Seluruh Dialog Browser Native:**
   - Mengganti 100% `alert()` dan `confirm()` pada `shifts.blade.php`, `printers/index.blade.php`, `kitchen.blade.php`, `tables.blade.php`, `receipt.blade.php`, `orders.blade.php`, dan `public/qr-order/menu.blade.php` dengan `AppAlert.confirm()`, `AppAlert.success()`, dan `AppAlert.error()`.
3. **Pembersihan Karakter Emoji Unicode:**
   - Mengganti emoji `⚡`, `📅`, dan lainnya di `terminal.blade.php` dengan semantic Lucide icons (`<i data-lucide="zap">`, `<i data-lucide="calendar">`).
4. **Proteksi Double-Submit pada Form Kasir:**
   - Menambahkan status `isSubmitting` / `:disabled="isSubmitting"` dengan spinner loading pada modal buka shift, tutup shift, mutasi kas, cetak struk, dan checkout.

---

### FASE 4: Hardening Public QR Order, Customer CRM Auto-Connect & Penegakan Context-Aware UI 20 Sektor (P2 - Sedang) — [COMPLETED ✅]

**Tujuan:** Mengamankan kanal pemesanan meja mandiri publik (`resources/views/public/qr-order`), mengotomasi pembuatan data pelanggan ke modul CRM (`Customer`), menegakkan penjagaan nomor telepon di frontend, dan menyesuaikan antarmuka secara sadar konteks untuk 20 sektor industri bisnis.

1. **Otomasi Lifecycle Pelanggan & Customer CRM Auto-Connect (`PosOrderService@createQrOrder`):**
   - Saat pesanan QR dikirim, sistem otomatis mencari atau membuat record pada tabel `customers` (`business_id`, `phone`, `name`).
   - Menautkan `order.customer_id = $customer->id` secara sah di database.
   - Mengakumulasikan `total_orders_count`, `total_spent`, dan poin loyalitas secara otomatis.
2. **Frontend Phone Number Guardrail & UX Pre-Validation (`menu.blade.php`):**
   - Penjagaan nomor telepon di depan: Minimal 10 digit, maksimal 15 digit.
   - Sanitasi otomatis: Hapus karakter non-numerik, spasi, dan dash secara real-time saat mengetik.
   - Format standar Indonesia: Awalan `08...`, `628...`, atau `+628...`.
   - Indikator visual interaktif dan penguncian tombol `[ Mulai Pilih Menu ]` / `[ Kirim Pesanan ]` (`disabled`) sampai input valid.
   - Penerapan kelas font `text-[16px] sm:text-xs` pada seluruh input untuk mencegah iOS Safari auto-zoom.
3. **Refactoring & Hardening `resources/views/public/qr-order/menu.blade.php`:**
   - Mengganti 6 dialog native `alert()` dengan Apple HIG floating toast & error banners.
   - Mengubah modal kustomisasi modifier dan modal keranjang belanja agar adaptif di Tablet/Desktop (tidak terkunci di `max-w-md`).
   - Memastikan respons instan dan auto-poll status QRIS TriPay tanpa freezing.
4. **Dinamisasi Tombol Layanan Vertikal pada Terminal Kasir:**
   - Mengganti tombol statis `Layanan Khusus (Bengkel / Laundry)` menjadi tombol adaptif berdasarkan `$business->template_code` / `industry_category`:
     - **Bengkel Otomotif (`service_workshop`):** `[ 🚗 Data Kendaraan & SPK Bengkel ]` (Plat, Model, Odometer, Montir).
     - **Laundry Kiloan (`service_laundry`):** `[ 🧺 Data Timbangan & Rak Cucian ]` (Kg, Rak, Estimasi Selesai).
     - **Apotek & Farmasi (`retail_pharmacy`):** `[ 💊 Data Batch & Aturan Pakai Obat ]` (Batch, ED, Dosis).
     - **Restoran / Cafe (`fnb_resto` / `fnb_cafe`):** Sembunyikan tombol layanan kendaraan/laundry, fokuskan pada Meja, Varian Rasa & Topping.
     - **Ritel / Minimarket (`retail_reseller`):** Sembunyikan seluruh modal layanan khusus non-ritel, maksimalkan kecepatan pemindaian barcode.
5. **Kondisionalisasi Menu Meja (`pos.tables`) & KDS Dapur (`pos.kitchen`):**
   - Menu Meja dan KDS hanya ditampilkan untuk sektor kuliner (F&B) atau bisnis yang mengaktifkan modul `pos_dinein` dan `kds`.
   - Untuk sektor Jasa, Ritel, dan Manufaktur, antarmuka terminal otomatis menyembunyikan tab meja dan KDS.

---

### FASE 5: Pengujian Otomatis Komprehensif, Hardening & Pembaruan Dokumentasi (P1 - Kritis) — [COMPLETED ✅]

**Tujuan:** Memvalidasi seluruh fungsi, memastikan 100% test pass, dan menyinkronkan dokumentasi 3-layer.

1. **Penulisan Test Suite Otomatis:**
   - `tests/Feature/Pos/PosSecurityAndAntiFraudTest.php`:
     - Test pencegahan SSRF pada printer LAN.
     - Test penolakan otorisasi jika PIN supervisor kosong (no default '1234').
     - Test penolakan manipulasi `unit_price` pada checkout.
     - Test verifikasi voucher valid vs tidak valid via API.
     - Test penutupan shift kasir blind count tanpa kebocoran ekspektasi sistem.
     - Test alur submit pesanan publik QR table order.
2. **Eksekusi Pengujian & Verifikasi Nyata:**
   - Menjalankan `php -l` pada seluruh file PHP yang dimodifikasi.
   - Menjalankan `php artisan test --filter=Pos` dan memastikan 0 error, 0 failure.
   - Menjalankan `php artisan route:list` untuk validasi keutuhan route.
3. **Pembaruan Dokumentasi 3-Layer:**
   - Layer 1: Catat entri lengkap di `docs/AiWorkHistory.md`.
   - Layer 2: Sinkronkan `docs/system/modules/pos.md` dan `docs/system/workflows/pos-sales-flow.md`.
   - Layer 3: Rangkum pembaruan pada `docs/SYSTEM_GUIDE.md`.

---

## 3. Matriks Berkas Terdampak (Affected Files Matrix)

| Berkas / Modul | Perubahan yang Dilakukan | Prioritas |
| :--- | :--- | :--- |
| `resources/views/public/qr-order/menu.blade.php` | Eliminasi 6 `alert()` native ke Apple HIG feedback, modal responsif tablet/desktop, normalisasi nomor HP. | P2 |
| `resources/views/public/qr-order/invalid-qr.blade.php` | Apple HIG styling & responsivitas. | P3 |
| `app/Http/Controllers/Web/Pos/PublicQrOrderWebController.php` | Penguatan validasi input, sanitasi nomor telepon, proteksi rate-limiting. | P1 |
| `resources/views/app/pos/shifts.blade.php` | Modal XXL 2-kolom (`max-w-5xl xl:max-w-6xl`), penegakan Blind Cash Count, eliminasi `alert()`, double-submit lock. | P1 |
| `resources/views/app/pos/printers/index.blade.php` | Modal XXL 2-kolom (`max-w-5xl xl:max-w-6xl`), eliminasi `alert()` / `confirm()` native ke `AppAlert`. | P2 |
| `resources/views/app/pos/tables.blade.php` | Modal XXL 2-kolom (`max-w-3xl lg:max-w-4xl`), eliminasi `confirm()` native ke `AppAlert`. | P2 |
| `resources/views/app/pos/terminal.blade.php` | Standar modal responsif XXL, eliminasi prefill shift close, adaptasi tombol 20 sektor, eliminasi emoji, validasi voucher. | P1 |
| `resources/views/app/pos/kitchen.blade.php` | Eliminasi `alert()` native ke `AppAlert`, gating visibilitas sektor F&B. | P2 |
| `resources/views/app/pos/receipt.blade.php` | Eliminasi `confirm()` native ke `AppAlert`, adaptasi metadata struk 20 sektor. | P2 |
| `resources/views/app/pos/orders.blade.php` | Eliminasi `confirm()` native ke `AppAlert`, visualisasi data pesanan kontekstual. | P2 |
| `app/Http/Controllers/Web/Pos/PosPrinterWebController.php` | Validasi SSRF IP privat/metadata, restriksi port standar ESC/POS, eliminasi default PIN '1234'. | P1 |
| `app/Http/Controllers/Web/Pos/PosOrderWebController.php` | Eliminasi fallback default PIN '1234' pada void/refund, penegakan Bcrypt verification. | P1 |
| `app/Http/Controllers/Web/Pos/PosTerminalWebController.php` | Penambahan endpoint asinkron `/pos/validate-voucher`, penyediaan metadata konteks industri 20 sektor. | P1 |
| `app/Domain/Pos/PosOrderService.php` | Proteksi client-side price tampering (server-side price resolution), validasi diskon manual. | P1 |
| `tests/Feature/Pos/PosSecurityAndAntiFraudTest.php` | Test suite pengujian otomatis untuk keamanan siber, anti-fraud, modal pop-up, dan QR table order. | P1 |
