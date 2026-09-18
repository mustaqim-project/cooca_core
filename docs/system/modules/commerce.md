# Modul Toko Online & E-Commerce (Commerce & Storefront)

> **Status:** VERIFIED  
> **Domain Terkait:** `app/Domain/Commerce/`, `app/Domain/LandingPage/`, `app/Domain/Product/`, `app/Domain/WhatsApp/`  
> **Tabel Basis Data:** `commerce_store_settings`, `commerce_payment_methods`, `commerce_orders`, `commerce_order_items`, `commerce_payment_proofs`, `commerce_shipping_rules`, `commerce_order_batches`, `commerce_reservations`, `global_customers`, `customer_carts`, `customer_cart_items`

---

## 1. Tujuan & Nilai Bisnis

Modul E-Commerce & Storefront menyediakan kanal penjualan online mandiri (*Self-Service Online Ordering*) untuk setiap toko atau cabang bisnis yang terdaftar di Cooca. Pembeli dapat membuka halaman publik toko (`/b/{slug}`), memilih produk, memasukkan pesanan ke keranjang belanja, memilih opsi pengiriman atau jadwal pengambilan (*scheduled batch*), dan melakukan checkout mandiri langsung dari smartphone tanpa perlu menunggu respon manual kasir.

---

## 2. Arsitektur Storefront & Keranjang Multi-Tenant

Sistem menerapkan arsitektur **Global Identity dengan Multi-Tenant Shopping Cart**:

```
[Pembeli Storefront]
       │ (Login Global / Google SSO / WA OTP)
       ▼
[GlobalCustomer: global_customers]
       │
       ├─── Belanja di Toko A ──► [customer_carts: business_id = Toko A] ──► [Cart Items A]
       │
       └─── Belanja di Toko B ──► [customer_carts: business_id = Toko B] ──► [Cart Items B]
```

* **Identitas Tunggal Pelanggan:** Satu akun pembeli dapat digunakan untuk bertransaksi di toko-toko UMKM yang berbeda dalam ekosistem Cooca.
* **Isolasi Keranjang Belanja:** Keranjang belanja secara otomatis tersekat per `business_id` aktif untuk mencegah tercampurnya produk dari toko yang berlainan dalam satu pesanan.

---

## 3. Fitur Utama Modul E-Commerce

### 3.1 Etalase Toko Digital Publik (`/b/{slug}`)
* Tampilan katalog responsif mobile berdesain **Apple HIG v2.0 Bento Grid**.
* Menampilkan foto produk, deskripsi, varian harga, ketersediaan stok real-time, jam operasional toko, dan lokasi peta.

### 3.2 Opsi Pemenuhan Pesanan Fleksibel (Fulfillment Modes)
1. **Pesan Antar / Kurir Pengiriman (*Delivery* via Biteship.com Integrations API):**  
   Menggantikan metode pengiriman manual toko dengan integrasi resmi **Biteship.com**. Tarif ongkos kirim dihitung secara *real-time* via Biteship Rates API (`POST /v1/rates/couriers`) berdasarkan kode pos asal toko, kode pos tujuan pembeli, koordinat GPS, serta total berat belanjaan (gram). Mendukung kurir multi-ekspedisi nasional (JNE, SiCepat, J&T, AnterAja, GoSend, GrabExpress).
2. **Ambil Sendiri di Toko (*Pickup / Takeaway*):**  
   Pelanggan memilih waktu estimasi pengambilan barang di outlet.
3. **Batch Pesanan Terjadwal (*Scheduled Order Batches*):**  
   Sangat cocok untuk bisnis katering, bakery, PO baju, atau hampers dengan batas waktu pemesanan (*cut-off time*) dan tanggal distribusi massal.
4. **Reservasi Meja & Layanan (*Commerce Reservations*):**  
   Pemesanan meja kafe/restoran atau reservasi jam layanan salon/bengkel dengan slot waktu teratur.

### 3.3 Alur Pembayaran & Unggah Bukti Transfer
* Mendukung Transfer Bank Manual (BCA, Mandiri, BRI, QRIS Merchant).
* Pembeli dapat mengunggah foto bukti pembayaran (`commerce_payment_proofs`).
* Merchant menerima notifikasi pesanan masuk dan dapat memverifikasi bukti bayar dengan tombol 1-klik:
  - `[ ✅ Verifikasi & Proses Pesanan ]`
  - `[ ❌ Tolak & Minta Bukti Ulang ]`

### 3.4 Portal Pelanggan & Anti-IDOR Shield (`/customer/*`)
* Pelanggan dapat memantau status pesanan secara mandiri: *Menunggu Pembayaran ➔ Menunggu Verifikasi ➔ Diproses ➔ Siap Diambil / Dikirim ➔ Selesai*.
* **Otentikasi Google SSO Mandiri (`/customer/auth/google`):** Pelanggan dapat masuk secara instan menggunakan akun Google mereka. Integrasi ini dikelola terpisah di Admin Console (`/admin/settings`) via opsi `allow_customer_google_login` dan callback URI `google_customer_redirect_uri` (`/customer/auth/google/callback`), terisolasi dari login Google Owner Bisnis.
* **IDOR Shield Guardrail:** Seluruh query pesanan pelanggan WAJIB memverifikasi bahwa pesanan tersebut terdaftar atas ID pelanggan global yang sedang login (`auth:customer`) dan nomor telepon terverifikasi. Akses pesanan milik orang lain diblokir dengan kode HTTP 403 Forbidden.

### 3.5 Pengaturan Toko Modular Bento & Kliring Saldo Settlement (`/storefront/settings`)
* **Arsitektur Sub-Tab 5 Bagian:** Mengurangi kepadatan antarmuka dengan membagi pengaturan etalase ke dalam 5 tab Bento Apple HIG:
  1. `tab-general`: Status toko buka/tutup, direktori Jelajah, ambang minimal order, batas auto-cancel, dan banner pengumuman.
  2. `tab-fulfillment`: Saklar Pengiriman (Delivery) dan Pengambilan (Pickup) dengan tautan aturan ongkir.
  3. `tab-features`: Selektor 4 mode bisnis untuk 20+ industri (Request Order, Scheduled Pre-Order, B2B PO Batch, Reservasi Meja/Jasa).
  4. `tab-schedule`: Jam buka harian, interval slot waktu, lead time, cutoff, dan kalender batch pemesanan.
  5. `tab-payment`: Transparansi dual-channel, kartu saldo escrow gateway live (`gross`, `fee`, `net balance`), riwayat settlement, tombol penarikan saldo ke rekening bank (`/finance/settlements`), serta manajer rekening transfer manual.
* **Navigasi Adaptif:** Menu etalase "Ongkir & Pengiriman" dan "Reservasi & Booking" pada navbar etalase dan sidebar secara dinamis menyembunyikan diri jika fitur terkait tidak diaktifkan pada pengaturan toko.

### 3.6 Logistik Ekspedisi Terintegrasi Biteship.com (`/storefront/shipping` & Order Fulfillment)
* **Pusat Logistik Bento Apple HIG (`/storefront/shipping`):**
  - **Alamat Asal Penjemputan Toko (*Store Origin Address*):** Mengatur nama kontak PIC penanggung jawab toko, nomor telepon WhatsApp, alamat lengkap, kode pos 5-digit, koordinat Latitude/Longitude (via tombol auto-deteksi GPS browser), serta Biteship Area ID.
  - **Pencarian Wilayah Administratif (*Biteship Maps Search Area API*):** Autocomplete pencarian nama kelurahan, kecamatan, dan kota se-Indonesia (`GET /v1/maps/areas?countries=ID&input=...`) untuk mengaitkan toko secara presisi dengan `origin_area_id` resmi Biteship.
  - **Sinkronisasi Otomatis ke Biteship Locations API (`POST /v1/locations`):** Saat merchant menyimpan formulir alamat asal, sistem secara otomatis mendaftarkan/memperbarui lokasi fisik toko ke server Biteship dan menyimpan `origin_location_id`. Badge status menampilkan ID lokasi resmi Biteship yang aktif.
  - **Seleksi Kurir Ekspedisi Terpilih:** Merchant dapat mengaktifkan atau menonaktifkan ekspedisi favorit (JNE, SiCepat, J&T, AnterAja, GoSend, GrabExpress) melalui saklar Bento.
  - **Simulator Cek Ongkir Live:** Fitur uji coba tarif langsung untuk memverifikasi akurasi ongkos kirim ke kode pos tujuan pembeli sebelum etalase dibuka.
* **Seleksi Kurir Dinamis di Checkout Publik (`/b/{slug}`):**
  - Pembeli memasukkan kode pos tujuan 5-digit pada modal checkout bento.
  - Sistem memanggil API Biteship untuk menampilkan opsi kurir yang aktif lengkap dengan estimasi hari tiba (*ETD*) dan tarif riil.
  - Data kurir (`shipping_courier_code`, `shipping_courier_service`, `shipping_courier_name`, `destination_postal_code`, `shipping_cost`) dikunci saat checkout tersimpan.
* **Manajemen Pengiriman & Request Pickup di Detail Pesanan (`/storefront/orders/{order}`):**
  - Merchant dapat menekan tombol `[ 📦 Request Pickup Biteship ]` untuk mengirimkan payload order ke Biteship Order API (`POST /v1/orders`). Jika toko memiliki `origin_location_id`, sistem langsung mengirimkan `origin_location_id` untuk penjemputan instan yang akurat.
  - Sistem menyimpan ID pesanan Biteship (`biteship_order_id`), nomor resi pengiriman (`shipping_waybill_id`), tautan pelacakan langsung (`shipping_tracking_url`), dan status pengiriman (`shipping_status`).
  - Dilengkapi tombol sinkronisasi live status resi (`GET /v1/orders/:id`) dan tombol pembatalan penjemputan paket (`POST /v1/orders/:id/cancel`) dengan dialog konfirmasi aman.
* **Cetak Label Resi Pengiriman (Thermal 100x150mm & A4) (`/storefront/orders/{order}/shipping-label`):**
  - Menyediakan tampilan label siap cetak berstandar ekspedisi Indonesia yang kompatibel dengan printer stiker thermal (100x150 mm / 4x6 inci) dan kertas dokumen A4.
  - Menampilkan vector SVG Barcode murni (`BarcodeService`) yang dapat dipindai oleh scanner kurir, nomor resi AWB monospace tebal, nomor order, rincian alamat pengirim & penerima lengkap dengan kode pos 5 digit mencolok, total berat kg, packing slip item belanja, tanda FRAGILE, serta QR Code pelacakan live pelanggan.
* **Penerbitan Resi Manual & Auto-Resi Toko (`POST /storefront/orders/{order}/waybill`):**
  - Menyediakan modal "Input / Ubah Resi" yang fleksibel bagi merchant yang mengirim paket melalui loket counter fisik atau kurir internal toko.
  - Mendukung tombol "Auto Resi" dengan format standar COOCA: `CC[KODE_KURIR][YYMMDD][RANDOM4]`.
* **Pelacakan Mandiri Publik (`/storefront/order-tracking/{uuid}`):**
  - Menampilkan nama ekspedisi, tipe layanan, nomor resi AWB dengan tombol 1-klik salin (*copy to clipboard*), dan tombol pelacakan langsung ke portal tracking resmi Biteship.

---

## 4. Aturan Bisnis E-Commerce (Business Rules)

* **RULE-COMM-001 (Cart Isolation):** Satu sesi checkout hanya boleh memproses item dari satu tenant bisnis (`business_id`).
* **RULE-COMM-002 (Stock Reservation / Deduction):** Stok produk atau bahan baku resep BOM dipotong otomatis saat merchant mengubah status pesanan menjadi `paid` atau `processing`.
* **RULE-COMM-003 (Verified Contact Mandatory):** Pelanggan wajib memiliki nomor telepon atau email yang tervalidasi sebelum dapat menyelesaikan pesanan bernilai tinggi atau mengajukan reservasi.
* **RULE-COMM-004 (Dual Payment Channel Transparency):** Merchant wajib memiliki kejelasan pemisahan antara pembayaran otomatis payment gateway (yang masuk ke saldo kliring escrow terpusat) dan transfer manual (yang masuk langsung ke rekening pribadi/bank merchant).
* **RULE-COMM-005 (Biteship Live Rates & Logistics Scoping):** Perhitungan tarif ekspedisi wajib menyertakan kode pos toko asal (`origin_postal_code`) dan kode pos pembeli (`destination_postal_code`). Jika API Biteship mengalami gangguan jaringan atau akun dalam masa validasi, sistem mengaktifkan mekanisme *graceful fallback rate* dan simulasi pengiriman sandbox sehingga alur transaksi pelanggan tidak terputus.

---

## 5. Keterkaitan Lintas Modul

* **Ke Modul Inventory:** Memeriksa ketersediaan stok fisik secara langsung sebelum pembeli menyelesaikan checkout.
* **Ke Modul Finance:** Pesanan e-commerce yang diverifikasi lunas langsung memicu pencatatan uang masuk dan `AutoJournalService`, serta akumulasi saldo settlement yang siap ditarik via `/finance/settlements`.
* **Ke Modul POS:** Reservasi online dari storefront (`commerce_reservations`) secara langsung terintegrasi ke denah meja POS (`pos_tables`) dan modal terminal kasir.
* **Ke Modul WhatsApp:** Mengirimkan notifikasi invoice digital, nomor resi pengiriman, dan update status pesanan otomatis ke WhatsApp pembeli.
* **Ke Modul Shipping (Biteship.com):** Menghubungkan pesanan fisik secara langsung dengan 10+ jaringan ekspedisi logistik nasional (Rates API & Order API) untuk penjemputan paket otomatis dan live tracking resi AWB.

