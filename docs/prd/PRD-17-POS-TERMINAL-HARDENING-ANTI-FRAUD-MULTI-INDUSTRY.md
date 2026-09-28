# PRD-17: Penguatan Keamanan Siber, Proteksi Fraud Internal Kasir, Standar Modal Pop-Up XXL Apple HIG v2.0 & Integrasi QR Table Order 20 Sektor Industri

> **Dokumen ID:** `PRD-17-POS-TERMINAL-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY`  
> **Status:** PROPOSED / UPDATED WITH PUBLIC QR-ORDER & XXL MODAL AUDIT  
> **Tanggal Pembuatan:** 2026-09-28  
> **Penulis:** AI Architecture & Security Agent  
> **Modul Terkait:** Point of Sale (`resources/views/app/pos/`, `resources/views/public/qr-order/`, `app/Http/Controllers/Web/Pos/`, `app/Domain/Pos/`, `app/Domain/Printer/`)  
> **Standar Rujukan:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md), [`docs/system/workflows/pos-sales-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/pos-sales-flow.md)

---

## 1. Ringkasan Eksekutif & Latar Belakang Masalah

Modul Point of Sale (POS) dan kanal publik Pemesanan Meja Mandiri (*Public QR Table Ordering*) merupakan dua pilar utama transaksi harian UMKM di 20 sektor industri pada platform COOCA. Berdasarkan audit mendalam terhadap seluruh berkas view admin POS ([`resources/views/app/pos/`](file:///c:/laragon/www/cooca_core/resources/views/app/pos)) dan publik ([`resources/views/public/qr-order/`](file:///c:/laragon/www/cooca_core/resources/views/public/qr-order)), ditemukan empat klaster masalah kritis:

1. **Standar Modal Pop-Up Terlalu Sempit & Melanggar Bento Apple HIG v2.0 (Modal Size Violation):**
   - Aturan baku COOCA mewajibkan **Modal-First Full-Size Canvas XXL** (`max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px]` di Desktop dan Full-Width Bottom Sheet `w-full max-h-[95vh]` di Mobile).
   - Pada kondisi saat ini, banyak modal di `shifts.blade.php` (`max-w-lg`, `max-w-md`), `printers/index.blade.php` (`max-w-xl`, `max-w-md`), `tables.blade.php` (`max-w-sm`), `terminal.blade.php` (`max-w-lg`, `max-w-md`), dan `public/qr-order/menu.blade.php` (`max-w-sm`, `max-w-md`) berukuran sempit (*cramped modal*). Pada layar tablet dan desktop, modal ini terlihat seperti aplikasi ponsel kaku di tengah layar tanpa memanfaatkan ruang luas untuk menampilkan preview data, rincian biaya, atau panduan kontekstual.
2. **Celah Keamanan Siber & SSRF pada Hardware Printer:**
   - Input IP `interface_address` pada printer LAN menerima sembarang host tanpa validasi subnet, membuka celah *Server-Side Request Forgery* (SSRF) ke endpoint cloud metadata (`169.254.169.254`) atau socket internal server (`127.0.0.1`).
   - Otorisasi Supervisor PIN menggunakan fallback insecure `'1234'`, memungkinkan pembatalan transaksi (*void/refund*) atau pembukaan laci kas tanpa izin Owner.
3. **Pelanggaran Fraud Blind Cash Count & Client-Side Price Tampering:**
   - Modal tutup shift (`shifts.blade.php` dan `terminal.blade.php`) menampilkan dan mem-prefill ekspektasi kas sistem ke input kasir, melanggar SOP **Blind Cash Count** dan membuka celah penggelapan kas laci (*skimming*).
   - Validasi checkout backend menerima `unit_price` dari request klien tanpa verifikasi ke database harga katalog/cabang.
   - Perhitungan diskon voucher di browser dihitung secara mock/dummy (`Math.min(subtotal * 0.1, 50000)`) tanpa validasi asinkron server.
4. **Audit Terpadu Kanal Publik QR Table Order (`resources/views/public/qr-order`):**
   - Ditemukan **6 dialog native browser `alert()`** pada `public/qr-order/menu.blade.php`.
   - Modifiers selection, verifikasi stok real-time, dan status pembayaran QRIS TriPay memerlukan penanganan UI yang mulus dengan feedback modal Apple HIG tanpa reload.
5. **Kebocoran Kontekstual Antar 20 Sektor Industri (Cognitive Overload):**
   - Terminal kasir menampilkan tombol generik `Layanan Khusus (Bengkel / Laundry)` secara seragam untuk seluruh jenis usaha.
   - Sektor non-kuliner (Bengkel, Apotek, Konveksi, Toko Bangunan) masih terpapar tab Meja Makan dan KDS Dapur.

---

## 2. Tujuan & Sasaran Metrik Kinerja

1. **100% Full-Size Responsive Modal Canvas:** Seluruh modal pop-up pada POS dan QR Order beradaptasi menjadi format XXL 2-kolom di Desktop (`max-w-5xl`/`xl:max-w-6xl`), 2-kolom seimbang di Tablet (`max-w-3xl`/`max-w-4xl`), dan Full Bottom Sheet di Mobile (`w-full rounded-t-[28px] max-h-[95vh]`).
2. **Zero Dialog Browser Native & Zero Emoji:** 100% `alert()` dan `confirm()` pada `app/pos` dan `public/qr-order` diganti dengan `AppAlert` / custom Apple HIG Bento modals, serta pembersihan seluruh emoji Unicode.
3. **Zero SSRF & Zero Insecure Default:** Sanitizer IP menolak loopback/metadata cloud, dan penegakan PIN Supervisor 6 digit ter-hash Bcrypt tanpa fallback rentan.
4. **100% Strict Blind Cash Count:** Total kas sistem disembunyikan penuh dari kasir saat penutupan shift; kalkulasi selisih dieksekusi murni di backend.
5. **Server-Authoritative Price & Voucher Validation:** Validasi harga item dan voucher diskon 100% dihitung di backend.
6. **Zero Context Leakage:** Antarmuka terminal, struk, dan QR Order beradaptasi otomatis mengikuti `$business->template_code` dan kategori 20 sektor industri.

---

## 3. Spesifikasi Kebutuhan Fungsional (Functional Requirements)

### FR-01: Standar Modal Pop-Up XXL & Full-Size Responsive Canvas
* **FR-01.1 (Desktop >= 1024px):** Seluruh modal transaksi, pembukaan/penutupan shift kasir, konfigurasi printer, mutasi kas, tambah meja, dan pemesanan publik QR wajib berukuran **Full Layout XXL** (`max-w-5xl` s/d `xl:max-w-6xl`, rounded `rounded-[24px]`, max height `92vh`) dengan tata letak 2 kolom terpisah:
  - **Kolom Kiri (65%):** Formulir input utama (pecahan uang, interface printer, data pesanan/meja) dengan input font minimal 16px.
  - **Kolom Kanan (35%):** Kartu ringkasan live (*Live Summary Bento Card*), panduan operasional, dan indikator audit trail.
* **FR-01.2 (Tablet 640px–1023px):** Centered Responsive Bento Modal (`max-w-3xl` s/d `max-w-4xl`, rounded `rounded-[22px]`) dengan touch targets 44px–48px.
* **FR-01.3 (Mobile < 640px):** Apple Full-Responsive Bottom Sheet (`w-full inset-x-0 bottom-0 rounded-t-[28px] max-h-[95vh]`) dengan lebar 100% viewport, Apple grab bar indicator, input font 16px (anti-auto-zoom iOS), dan sticky action button 48px–52px.

### FR-02: Hardening Kanal Publik Pemesanan Meja QR (`public/qr-order`)
* **FR-02.1 (Eliminasi Dialog Native):** Mengganti seluruh 6 instance `alert()` pada `menu.blade.php` dengan Apple HIG Toast / Modal Sheet feedback.
* **FR-02.2 (Modal Kustomisasi Menu & Keranjang Adaptif):** Memperluas modal kustomisasi modifier dan keranjang belanja pada `menu.blade.php` agar responsif di layar tablet/desktop (tidak terkunci di `max-w-md`).
* **FR-02.3 (Validasi Nomor HP Indonesia):** Menambahkan normalisasi format nomor HP/WhatsApp pelanggan (`08...` / `62...`) sebelum order dikirim.
* **FR-02.4 (Sinkronisasi Real-Time Terminal POS):** Memastikan pesanan QR yang masuk langsung memicu audio chime dan banner notifikasi floating pada terminal kasir POS (`latestQrNotification`).

### FR-03: Hardening Keamanan Siber & Proteksi SSRF Printer Jaringan
* **FR-03.1:** Validasi input `interface_address` pada `PosPrinterWebController` wajib menolak alamat IP loopback (`127.0.0.0/8`, `::1`), alamat link-local/cloud metadata (`169.254.0.0/16`), dan port di luar port standar printer (9100, 515, 631).
* **FR-03.2:** Batasi timeout koneksi socket TCP (`NetworkConnector`) maksimal 2.0 detik.

### FR-04: Eliminasi Insecure Default PIN & Otorisasi Supervisor Terenkripsi
* **FR-04.1:** Dilarang keras menggunakan fallback PIN `'1234'`. Jika `$business->pos_supervisor_pin` kosong, sistem wajib menolak otorisasi sensitif dan memunculkan banner peringatan kepada Owner untuk mengatur PIN 6 digit.
* **FR-04.2:** Penyimpanan PIN Supervisor pada tabel `businesses` wajib menggunakan hashing Bcrypt (`Hash::make()`) dengan rate limit ketat (maksimal 5 percobaan gagal per menit).

### FR-05: Penegakan Strict Blind Cash Count pada Penutupan Shift
* **FR-05.1:** Pada view `shifts.blade.php` dan `terminal.blade.php`, elemen yang menampilkan ekspektasi kas sistem (`Total Harapan di Laci (Sistem)`) dan perhitungan selisih kas langsung (*live variance*) wajib disembunyikan dari peran Kasir.
* **FR-05.2:** Input nominal penutupan kasir wajib dimulai dari 0 (tanpa prefill nilai ekspektasi sistem). Kasir wajib memasukkan hitungan fisik nyata secara manual atau via kalkulator pecahan uang.
* **FR-05.3:** Khusus peran Owner / Supervisor yang memiliki izin khusus (`pos.supervisor_pin`), sediakan toggle terotorisasi `[ 👁️ Lihat Ekspektasi Sistem ]`.

### FR-06: Validasi Harga Sisi Server & Endpoint Asinkron Voucher
* **FR-06.1:** Pada `PosOrderService::checkout()`, sistem wajib memverifikasi ulang harga setiap produk katalog terhadap `Product::selling_price` atau `BranchProductPrice` atau `product_channel_prices` aktif, mengabaikan manipulasi `unit_price` dari payload browser.
* **FR-06.2:** Buat endpoint API `/pos/validate-voucher` yang memvalidasi keabsahan voucher secara real-time (tanggal aktif, minimal transaksi, sisa kuota) dan mengembalikan nominal potongan pasti ke antarmuka terminal kasir.

### FR-07: Penataan Antarmuka Sadar Konteks (Context-Aware UI) 20 Sektor Industri
Sistem memeriksa `$business->template_code` / `industry_category` dan menyesuaikan komponen antarmuka kasir:
* **Sektor Kuliner & F&B (7 Sektor):**
  - Tampilkan Bar Saluran Penjualan (`Dine In`, `Takeaway`, `GoFood`, `GrabFood`, `ShopeeFood`).
  - Tampilkan Tombol Meja & Reservasi Tamu (`pos.tables`).
  - Tampilkan Modal Resep Modifier (Topping, Suhu, Rasa).
* **Sektor Jasa Harian - Bengkel Otomotif (`service_workshop`):**
  - Tampilkan tombol utama: `[ 🚗 Data Kendaraan & SPK Bengkel ]` (Plat, Model, Odometer, Montir).
* **Sektor Jasa Harian - Laundry Kiloan (`service_laundry`):**
  - Tampilkan tombol utama: `[ 🧺 Data Cucian & Rak Laundry ]` (Kg, Rak, Estimasi Selesai).
* **Sektor Retail - Apotek & Farmasi (`retail_pharmacy`):**
  - Tampilkan tombol detail per item: `[ 💊 Data Batch & Dosis Obat ]` (Batch, ED, Dosis).
  - Tampilkan banner peringatan resep dokter untuk obat keras Golongan G.
* **Sektor Manufaktur & Tailor Custom (`mfg_garment`, `mfg_tailor_custom`, `mfg_printing`):**
  - Tampilkan opsi DP (Down Payment) / Termin dan pencatatan spesifikasi kustom (Ukuran, Bahan, Warna).

### FR-08: Otomasi Lifecycle Pelanggan pada Public QR Order (Customer CRM Auto-Connect)
* **FR-08.1 (Auto-Upsert Database Pelanggan):** Setiap kali pelanggan menginput Nama dan Nomor HP pada saat scan QR meja, sistem (`PosOrderService::createQrOrder`) wajib secara otomatis mencari atau membuat entitas [`Customer`](file:///c:/laragon/www/cooca_core/app/Models/Customer.php) di bawah tenant (`business_id`) yang bersangkutan.
* **FR-08.2 (Tautkan Customer ID):** `PosOrder` wajib menautkan `customer_id` yang valid (bukan hanya teks mentah `customer_name_guest` dan `customer_phone_guest`).
* **FR-08.3 (Akumulasi Lifetime Value & Poin Loyalitas):** Setiap transaksi QR Order yang diselesaikan wajib otomatis mengupdate `total_orders_count`, `total_spent`, dan `points_balance` pada profil `Customer`.
* **FR-08.4 (Kesiapan Struk Digital WhatsApp):** Nomor HP yang terhubung otomatis menjadi target pengiriman kuitansi / struk digital resmi via WhatsApp gateway.

### FR-09: Guardrail Nomor Telepon & UX Penjagaan di Depan (Frontend Pre-Validation)
* **FR-09.1 (Ketentuan Minimal Karakter):** Penjagaan ketat di depan (Client-side) dengan batas minimal 10 digit (contoh: `0812345678`) dan maksimal 15 digit.
* **FR-09.2 (Sanitasi Input Otomatis):** Input nomor telepon otomatis menghapus karakter spasi, tanda hubung (`-`), dan huruf secara instan saat pengguna mengetik.
* **FR-09.3 (Format Standar Indonesia):** Validasi awalan nomor HP wajib `08...`, `628...`, atau `+628...`.
* **FR-09.4 (Indikator Interaktif & Button Lock):** Tombol `[ Mulai Pilih Menu ]` dan `[ Kirim Pesanan ]` berstatus terkunci (`disabled`) jika nomor HP < 10 digit, disertai helper text informatif dengan ikon Lucide `info` / `check-circle`.
* **FR-09.5 (Anti Auto-Zoom iOS Safari):** Seluruh input pada layar QR Order wajib menerapkan kelas tipografi `text-[16px] sm:text-xs` untuk mencegah browser iOS melakukan zoom paksa.

---

## 4. Matriks Do's & Don'ts 20 Sektor Industri pada Terminal POS & QR Order

| Klaster Industri | Sektor Usaha Terkait | Do's (Wajib Ditegakkan) | Don'ts (Dilarang Ditampilkan) |
| :--- | :--- | :--- | :--- |
| **Klaster 1: Kuliner & F&B** | Resto, Cafe, Bakery, Cloud Kitchen, Catering, Frozen Food, Diet Catering | Denah Meja Dine-in, Reservasi Storefront, QR Standee Meja, Multi-Harga GoFood/GrabFood/ShopeeFood, Resep Modifiers (Suhu/Topping), Batch Prep Sheet | Dilarang menampilkan Form Plat Nomor Kendaraan, Montir Bengkel, Timbangan Laundry, atau Dosis Obat Apotek |
| **Klaster 2: Jasa Harian** | Bengkel Otomotif, Barbershop/Salon, Laundry Kiloan, Cuci Mobil | Bengkel: Plat Nomor, Model, Odometer KM, Montir. Laundry: Timbangan Kg, Rak, Estimasi Selesai. Salon: Kapster & Durasi | Dilarang menampilkan Denah Meja Makan Resto, KDS Dapur Bar, atau Kanal Delivery Makanan Luar |
| **Klaster 3: Retail & Toko** | Minimarket / Toko Kelontong, Apotek & Alkes | Minimarket: Barcode scanner kilat, bulk qty. Apotek: Nomor Batch, Expired Date (ED), Aturan Pakai, BPOM Hard-Lock Obat Keras | Dilarang menampilkan Meja Dine-in, KDS Dapur, atau Montir Bengkel |
| **Klaster 4: Manufaktur & Tailor** | Konveksi, Bubut Logam, Mebel, Kerajinan, Percetakan, Kosmetik, Tailor | Opsi Pembayaran DP/Termin, Spesifikasi Kustom (Ukuran PxL/Meteran, Bahan, Warna), Nomor SPK Produksi & HPP Resep BOM | Dilarang memaksakan workflow ritel kilat tanpa kolom spesifikasi kustom; dilarang denah meja resto |
| **Klaster 5 & 6: Proyek, Distribusi & Agri** | Agency, Kontraktor, Event Organizer, Distributor FMCG, Pertanian | Verifikasi Limit Piutang (Kasbon/Pay Later), Multi-Gudang Stok Tracking, Konversi Penawaran/Invoice Proyek | Dilarang menampilkan Meja Restoran atau KDS Dapur |

---

## 5. Rencana Pengujian & Verifikasi Kualitas

1. **Unit & Security Testing:**
   - Uji blokir SSRF pada input interface printer (`169.254.169.254`, `127.0.0.1`, `localhost`).
   - Uji penolakan otorisasi jika PIN supervisor kosong (no default '1234').
   - Uji proteksi manipulasi harga item pada endpoint `/pos/checkout`.
   - Uji normalisasi nomor HP dan auto-upsert Customer model pada `/order/table/{qrToken}`.
2. **Functional & Anti-Fraud Testing:**
   - Uji penutupan shift: pastikan data ekspektasi kas tidak bocor di HTML/JS kasir standar.
   - Uji verifikasi voucher asinkron `/pos/validate-voucher`.
   - Uji alur pemesanan publik QR meja (`/t/{qrToken}/order`) hingga masuk ke antrean terminal POS dan KDS Dapur.
3. **UI/UX HIG Testing:**
   - Pastikan 0 kemunculan `alert()` / `confirm()` native pada seluruh berkas view POS dan QR Order.
   - Pastikan 0 kemunculan emoji Unicode pada tombol, judul, dan badge.
   - Verifikasi modal pop-up berukuran XXL (`max-w-5xl`/`xl:max-w-6xl` di Desktop, `max-w-3xl`/`max-w-4xl` di Tablet, dan Bottom Sheet `w-full` di Mobile).
   - Verifikasi penjagaan nomor HP minimal 10 digit di frontend QR Order dan pencegahan auto-zoom iOS.

