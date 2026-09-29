# PRD-20: PENATAAN ARSITEKTUR KOMPREHENSIF STOREFRONT PUBLIK COOCA
## (Multi-Tenant Security IDOR Hardening, Dynamic Context-Aware Auto-Hiding 20 Sektor Industri & Multi-Language i18n/l10n ID/EN)

---

## 1. Metadata Dokumen & Referensi Induk
- **Dokumen ID:** `PRD-20`
- **Modul Utama:** Public Storefront & Commerce Portal
- **Folder Sasaran:** [`resources/views/public/storefront/`](file:///c:/laragon/www/cooca_core/resources/views/public/storefront), `app/Http/Controllers/Web/Storefront/`, `app/Http/Controllers/Web/Commerce/`, `lang/id/`, `lang/en/`
- **Master Directive:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md)
- **Status:** APPROVED & READY FOR STEP-BY-STEP IMPLEMENTATION
- **Prioritas:** P1 (Kritis)
- **Tanggal:** 2026-09-29

---

## 2. Business Objective & Executive Summary
Storefront Publik COOCA (diakses melalui `https://cooca.id/{slug-bisnis}`) adalah antarmuka garda terdepan tempat pelanggan berinteraksi, memesan produk/layanan, melakukan pembayaran, mengunggah bukti transfer, dan melacak pesanan.

Tujuan utama PRD-20 adalah:
1. **Mengeliminasi Celah Keamanan & Kebocoran Multi-Tenant:** Memastikan 100% data yang di-query (produk, layanan, meja, opsi bayar, artikel blog) diisolasi secara ketat berdasarkan `business_id` aktif.
2. **Menegakkan Dynamic Context-Aware Auto-Hiding:** Menghilangkan elemen atau alur yang tidak relevan dengan klaster industri bisnis aktif (misal: menyembunyikan "Makan di Tempat" & Meja pada toko Retail, Bengkel, Apotek, dan Konveksi).
3. **Mencapai 100% Zero Hardcoded Strings (i18n & l10n):** Menyediakan kamus modular dwibahasa (`lang/id/storefront.php` dan `lang/en/storefront.php`), menginjeksi `window.COOCA_I18N` untuk Alpine.js, serta memformat angka, mata uang, dan tanggal secara adaptif terhadap locale aktif.
4. **Meningkatkan Keamanan Frontend & Anti-Fraud:** Mengamankan passing data ke Alpine.js via `@js()` directive, mencegah double-submission, dan menjaga integritas harga transaksi dari database.

---

## 3. User Personas & User Stories

### A. Persona
1. **Pelanggan Tamu (Public Guest):** Pelanggan yang mengunjungi etalase publik tanpa akun dan ingin langsung checkout cepat (*frictionless shopping*).
2. **Pelanggan SSO Terverifikasi (GlobalCustomer):** Pelanggan yang login via Google SSO untuk riwayat pesanan dan program loyalti lintas-toko.
3. **Pelanggan Internasional / Ekspatriat:** Pengguna yang membutuhkan tampilan berbahasa Inggris (`en`) dan pemformatan mata uang/tanggal yang jelas.
4. **Merchant / Pemilik Usaha UMKM:** Menginginkan etalase toko yang profesional, aman dari fraud kasir/pembeli, dan sesuai dengan sektor industrinya.

### B. User Stories
- *Sebagai Pelanggan Publik*, saya ingin melihat katalog dan artikel resmi milik toko terkait tanpa tercampur dengan konten bisnis lain.
- *Sebagai Pelanggan Bahasa Inggris*, saya ingin seluruh teks antarmuka, notifikasi, dan status pesanan otomatis tampil dalam Bahasa Inggris saat locale `en` dipilih.
- *Sebagai Merchant Retail/Bengkel*, saya tidak ingin pembeli melihat opsi "Makan di Tempat (Dine-In)" atau pilihan meja restoran pada saat checkout.
- *Sebagai Kasir/Owner*, saya ingin setiap transaksi online memotong stok dan mencatat jurnal secara aman dan otomatis.

---

## 4. Functional Specifications (Spesifikasi Fungsional)

### 4.1. Lapisan Multi-Tenant Security & IDOR Shield
- **Scoping Wajib:** Semua query Eloquent pada `PublicStorefrontController`, `PublicOrderTrackingController`, `PublicReservationController`, dan `CommerceGroupOrderWebController` **wajib** menyertakan filter `where('business_id', $business->id)`.
- **Perbaikan Query Post:** Query `Post` pada method `PublicStorefrontController@home` wajib di-scope menggunakan `Post::when($hasBusinessId, fn ($q) => $q->where('business_id', $business->id))`.
- **XSS-Safe Directives:** Seluruh interaksi passing object PHP ke Alpine.js (`$store.cart.add(...)`, `storefrontPopupModal(...)`) dilarang menggunakan `addslashes()` dalam string template; wajib menggunakan `@js([...])`.

### 4.2. Lapisan Dynamic Context-Aware Auto-Hiding (20 Sektor Industri)
- **Klaster Kuliner (F&B):** Opsi "Makan di Tempat" (`dine_in`) dan pilihan meja (`pos_tables`) hanya diaktifkan jika `$business->industry_category` termasuk dalam klaster F&B (`['fnb', 'restaurant', 'cafe', 'bakery', 'culinary']`) atau `$business->isModuleEnabled('pos_dining')`.
- **Klaster Non-Kuliner (Retail, Otomotif, Jasa, Manufaktur, Agribisnis):** Opsi Dine-in otomatis disembunyikan pada checkout. Pilihan pemenuhan pesanan berfokus pada Pengiriman ke Alamat (*Delivery*) dan Ambil di Toko (*Pickup*).
- **Adaptasi Terminologi Reservasi:** 
  - F&B: *Reservasi Meja & Ruangan* (Jumlah Tamu).
  - Salon/Klinik/Jasa: *Booking Jadwal Layanan / Treatment* (Jumlah Pasien/Klien).
  - Bengkel/Otomotif: *Booking Jadwal Servis & Perawatan* (Nomor Polisi / Kendaraan).

### 4.3. Lapisan Multi-Bahasa Full-Stack (i18n & l10n ID/EN)
- **Penyusunan Kamus Modular:** Berkas `lang/id/storefront.php` dan `lang/en/storefront.php` memuat lebih dari 120 pasang kunci terjemahan terstruktur dalam kelompok:
  - `nav` (Navigasi, akun, logout, keranjang)
  - `hero` (Banner, badge verifikasi, jam buka)
  - `catalog` (Pencarian, filter, sortir, harga, keranjang)
  - `product_detail` (Galeri, SKU, ketersediaan, WhatsApp inquiry)
  - `checkout` (Metode penerimaan, form pengiriman, pembayaran, rincian biaya)
  - `tracking` (State machine status pesanan, instruksi gateway, upload bukti, split bill)
  - `reservation` (Form reservasi, tanggal, jam, konfirmasi)
  - `contact` (Saluran komunikasi, alamat, jam operasional)
  - `about` (Cerita brand, komitmen mutu, galeri)
  - `blog` (Artikel, tips, pembaca, share button)
  - `messages` (Pesan validasi, flash notification, alert)
- **Injeksi JavaScript Global:** Layout `layouts/app.blade.php` menyuntikkan objek konfigurasi bahasa:
  ```html
  <script>
      window.COOCA_I18N = @js(__('storefront.messages'));
  </script>
  ```
- **Lokalisasi Backend Controller:** Pesan validasi form dan respon JSON di `PublicOrderTrackingController` dan `PublicReservationController` menggunakan helper `__('storefront....')`.

---

## 5. Non-Functional Specifications (Spesifikasi Non-Fungsional)

1. **Performa & Latensi:**
   - Waktu render halaman etalase toko < 80ms pada koneksi 4G standar.
   - Zero N+1 query melalui pemanfaatan eager loading (`with(['category', 'images'])`).
2. **Kepatuhan Desain Bento Apple HIG:**
   - Geometri squircle kontinu, tombol aksi dengan touch-target minimum 44px–52px.
   - Tipografi angka dengan CSS font feature settings `tabular-nums`.
   - Zero emoji pada antarmuka sistem (hanya menggunakan ikon semantik Lucide).
3. **Ketahanan Error & Anti-Panic Microcopy:**
   - Pesan error disajikan dengan nada ramah, solutif, dan jelas (*No-Panic Microcopy*).
   - Seluruh form dilindungi dari pengiriman ganda via status button `:disabled="isSubmitting"`.

---

## 6. Acceptance Criteria (Kriteria Keberterimaan)

- [x] Dokumen PRD dan Rencana Implementasi terdokumentasi lengkap di direktori `docs/`.
- [ ] Berkas `lang/id/storefront.php` dan `lang/en/storefront.php` terisi lengkap dan valid secara sintaksis.
- [ ] Seluruh 11 berkas Blade storefront di `resources/views/public/storefront/` bebas 100% dari hardcoded string bahasa Indonesia mentah.
- [ ] Query `Post` di `PublicStorefrontController@home` di-scope ke `business_id` aktif.
- [ ] Opsi Dine-in dan meja otomatis tersembunyi untuk bisnis non-F&B.
- [ ] Pengujian otomatis via `php artisan test` lolos 100% (0 error, 0 failure).
