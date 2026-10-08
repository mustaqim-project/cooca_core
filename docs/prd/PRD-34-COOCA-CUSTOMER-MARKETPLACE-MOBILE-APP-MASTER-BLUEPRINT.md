# COOCA — CUSTOMER MARKETPLACE MOBILE APP
## Master Product Requirements Document (PRD), Architecture & Implementation Blueprint

---

## 1. Product Vision & Positioning
**COOCA** adalah aplikasi mobile marketplace resmi ekosistem COOCA yang ditujukan khusus bagi **Konsumen / Pembeli (Buyers / Customers)**. Aplikasi ini memungkinkan konsumen menemukan, menjelajah, memilih, membeli, membayar, melacak, dan memberikan ulasan terhadap produk-produk berkualitas dari seluruh merchant UMKM terpercaya yang terdaftar di jaringan COOCA.

### Core Architecture & Positioning
* **Native COOCA Marketplace Milik Sendiri:** COOCA Customer App adalah pasar digital mandiri (*first-party proprietary marketplace*). Pembeli bertransaksi langsung dengan toko UMKM mitra COOCA.
* **Bukan Konektor / Agregator Shopee / Tokopedia / TikTok:** Aplikasi ini **tidak memiliki** integrasi login, checkout, keranjang, atau penarikan data dari Shopee, Tokopedia, maupun TikTok Shop. Seluruh katalog produk, keranjang, pesanan, dan sistem pembayaran terhubung langsung ke **COOCA Core Backend**.
* **Pemisahan Tegas Dua Aplikasi:**
  - **Cooca My Own:** Aplikasi sistem operasi bisnis untuk pemilik toko dan staf.
  - **Cooca:** Aplikasi belanja konsumen publik.

---

## 2. Benchmark Kualitas & Desain

Meskipun mengadopsi standar alur kerja (*workflows*) kelas dunia dari Shopee dan Tokopedia (kemudahan pencarian, varian produk, keranjang multi-toko, kalkulasi ongkos kirim real-time, gateway pembayaran instan, dan pelacakan kurir), COOCA Customer App memiliki identitas visual khas:
* **Apple Human Interface Guidelines (HIG) Bento UI:** Bersih, lapang, modern, bebas dari banner berkedip norak (*anti-clutter*), tanpa warna gradasi berlebihan, dan bebas emoji di teks sistem.
* **Ergonomi Jempol (Thumb-Zone Friendly):** Tombol aksi utama (*Add to Cart, Buy Now, Checkout*) menempel di bagian bawah layar (*Sticky Bottom Action Bar*) dengan tinggi minimal 50px–52px.
* **Aksesibilitas & Kecepatan:** Transisi mulus 60 FPS, pemuatan gambar progresif (*lazy-load with blurred thumbnail placeholder*), dan font minimal 16px pada form input untuk kenyamanan visual.

---

## 3. Launch Experience & Identity Model

### Splash & Onboarding
* Saat aplikasi pertama kali dibuka:
  ```
  Splash Screen [Logo COOCA Elegan] ➔ Beranda Marketplace COOCA
  ```
* **Tanpa Pemilih Role:** Aplikasi ini 100% untuk pembeli, sehingga **DILARANG** menampilkan pertanyaan *"Apakah Anda Pemilik Usaha atau Pembeli?"*. Pengguna langsung disambut di etalase marketplace.

### Guest Browsing vs. Authenticated Actions
* **Guest Browsing Bebas:** Pembeli dapat menjelajahi beranda, mencari produk, melihat profil toko, membaca ulasan, dan menghitung estimasi ongkos kirim tanpa wajib login.
* **Gated Actions (Wajib Login):** Tindakan berikut secara elegan memicu modal login/registrasi:
  1. Menambahkan barang ke Keranjang (*Add to Cart*) atau Beli Sekarang (*Buy Now*).
  2. Melakukan *Checkout* pesanan.
  3. Memasukkan produk ke *Wishlist*.
  4. Memberikan *Review & Rating*.
  5. Mengakses halaman *Pesanan Saya (My Orders)* dan profil.

---

## 4. Customer Identity & Authentication

Menggunakan model **Global Identity Architecture** yang sudah teruji di backend COOCA:

```
[Pembeli COOCA Customer App]
       │
       ├── Google OAuth SSO (Cross-Store Identity: /customer/auth/google)
       └── WhatsApp OTP Verification (/customer/otp)
       ▼
[GlobalCustomer Model: global_customers]
       ├── Multiple Addresses (/customer/addresses)
       ├── Customer Wishlists
       └── Notification Preferences
```

### Fitur Otentikasi Pembeli
1. **Google Single Sign-On (SSO):** Masuk 1-klik menggunakan akun Google yang aman via OAuth 2.0.
2. **Nomor WhatsApp + OTP Seumur Hidup (*Lifetime WA OTP*):** Verifikasi nomor telepon pembeli melalui kode OTP 6-digit WhatsApp (dikirim melalui official Meta WhatsApp Cloud API). Sekali terverifikasi, nomor ponsel pembeli terhubung permanen.
3. **Manajemen Profil & Keamanan Akun:**
   - Ubah nama, email, nomor telepon WhatsApp (dengan re-verifikasi OTP jika nomor diubah).
   - Buku Alamat Lengkap (*Multi-Address Book*).
   - Hapus Akun (*Delete Account*) sesuai kepatuhan privasi data Apple App Store & Google Play Store.

---

## 5. Mobile Information Architecture (IA)

Navigasi utama menggunakan **Bottom Navigation Bar 5-Tab**:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   COOCA CUSTOMER APP — BOTTOM NAVBAR                   │
├────────────────────────────────────────────────────────────────────────┤
│ 1. [ Beranda (Home) ]                                                  │
│    • Search Bar & Location Chip (Kecamatan / Kota Saya)                │
│    • Carousel Banner Promo Kurasi COOCA                                │
│    • Kategori Pilihan (Grid Bulat / Squircle Apple)                    │
│    • Toko UMKM Pilihan & Merchant Terdekat (Nearby Stores)             │
│    • Produk Terlaris, Rekomendasi Pintar, Flash Deals                  │
│                                                                        │
│ 2. [ Kategori (Categories) ]                                           │
│    • Taksonomi Kategori Induk ➔ Subkategori ➔ Filter Spesifik          │
│    • Direktori Industri (Kuliner, Fashion, Gadget, Kerajinan, dll.)    │
│                                                                        │
│ 3. [ Keranjang (Cart) ]                                                │
│    • Multi-Merchant Grouped Cart (Item dikelompokkan rapi per toko)    │
│    • Checkbox Pilih Semua per Toko / Pilih Satuan                      │
│    • Subtotal Real-Time & Tombol Checkout Cepat                        │
│                                                                        │
│ 4. [ Pesanan Saya (Orders) ]                                           │
│    • Tab Status: Belum Bayar, Diproses, Dikirim, Selesai, Dibatalkan   │
│    • Kartu Rincian Pesanan, Unggah Bukti Transfer, Live Tracking Resi │
│    • Tombol Beli Lagi, Ajukan Komplain/Retur, Tulis Ulasan             │
│                                                                        │
│ 5. [ Akun Saya (Profile) ]                                             │
│    • Informasi Pribadi & Status Verifikasi WhatsApp                   │
│    • Buku Alamat Pengiriman (Pilih Alamat Utama)                       │
│    • Wishlist & Produk Favorit                                         │
│    • Voucher Saya & Pengaturan Notifikasi                             │
│    • Pusat Bantuan (Help Center) & Live Chat Support                  │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 6. End-to-End User Workflows

### 6.1 Product Discovery & Search Workflow
1. **Search Bar Responsif:** Mendukung pencarian nama produk, SKU, brand, dan nama toko.
2. **Search Suggestions & Recent Queries:** Menampilkan riwayat pencarian terakhir dan rekomendasi kata kunci terpopuler saat kolom pencarian ditekan.
3. **Filter & Sorting Dinamis:**
   - Urutkan: *Paling Sesuai, Terlaris, Harga Terendah, Harga Tertinggi, Ulasan Tertinggi, Terbaru*.
   - Filter: *Rentang Harga (Rp Min – Rp Max), Rating Bintang (4+), Lokasi Merchant (Provinsi/Kota), Opsi Pengiriman (Instan / Reguler / Ambil di Toko)*.

### 6.2 Product Detail Page (PDP)
* **Galeri Media Interaktif:** Carousel foto beresolusi tinggi dengan indikator halaman titik, kemampuan *pinch-to-zoom*, dan thumbnail preview.
* **Harga & Diskon Transparan:** Menampilkan harga coret jika ada potongan harga, harga grosir bertingkat (*Tier Pricing*), dan estimasi poin loyalitas yang didapat.
* **Selektor Varian & Add-On (Bottom Sheet):** Pilihan warna, ukuran, level rasa, atau topping tambahan dengan update harga dan kalkulasi sisa stok secara real-time.
* **Estimasi Ongkos Kirim:** Masukkan kelurahan/kode pos tujuan untuk melihat perkiraan ongkos kirim kurir Biteship (JNE, SiCepat, GoSend) langsung di PDP.
* **Profil Merchant:** Nama toko, badge verifikasi, rating toko, lokasi kota asal pengiriman, total produk, dan tombol `[ Kunjungi Toko ]`.
* **Ulasan Pembeli Terverifikasi (*Verified Reviews*):** Rating bintang 1–5, foto ulasan asli dari pembeli, dan respon penjual.

### 6.3 Multi-Merchant Grouped Shopping Cart
Sesuai standar marketplace terkemuka, satu keranjang belanja menampung barang dari beberapa toko sekaligus, namun **dikelompokkan dengan batas toko yang tegas (*Strict Merchant Boundaries*)**:

```
┌─────────────────────────────────────────────────────────────┐
│ [✓] Kopi Senja Nusantara (Jakarta Selatan)                   │
│   ├── [✓] Kopi Susu Gula Aren 1 Liter (Qty: 2)  Rp 150.000  │
│   └── [✓] Croissant Butter (Qty: 1)             Rp  28.000  │
│   Subtotal Toko: Rp 178.000 | [ Checkout Toko Ini (3 item) ]│
├─────────────────────────────────────────────────────────────┤
│ [ ] Dapur Sambal Bu Broto (Surabaya)                        │
│   └── [ ] Sambal Bawang Botol 150g (Qty: 3)     Rp  75.000  │
│   Subtotal Toko: Rp  75.000 | [ Checkout Toko Ini (3 item) ]│
└─────────────────────────────────────────────────────────────┘
```

* **Independensi Pesanan:** Pembeli dapat mencentang satu atau beberapa toko untuk checkout. Setiap toko yang di-checkout menghasilkan entitas `CommerceOrder` tersendiri di backend, menjamin tidak ada pencampuran operasional, ongkir, atau resi antar pedagang yang berbeda.

### 6.4 Checkout, Shipping & Payment Lifecycle
Alur checkout dirancang aman dari manipulasi harga (*zero client trust*):

```
Keranjang Belanja 
       ▼
Pilih / Tambah Alamat Pengiriman (GlobalCustomerAddress)
       ▼
Pilih Metode Pemenuhan (Delivery Kurir Biteship / Pickup di Toko)
       ▼
Panggilan Real-Time API Biteship (Tarif Kurir Resmi: JNE, SiCepat, J&T, GoSend)
       ▼
Input Voucher Promo Toko / Koin Diskon
       ▼
Pilih Metode Pembayaran:
   ├── Otomatis: TriPay Gateway (QRIS Instan, Virtual Account BCA/BRI/Mandiri, E-Wallet)
   └── Manual: Transfer Bank Toko (BCA/Mandiri) + Upload Bukti Bayar
       ▼
Konfirmasi Pesanan (Backend Locking: Stock, Price, Shipping Cost)
       ▼
Halaman Instruksi Pembayaran (Nomor VA / QRIS / Batas Waktu Bayar)
```

### 6.5 Order Tracking & Waybill Lifecycle
Pelacakan pesanan menyajikan timeline status visual:
1. **Menunggu Pembayaran (*Pending Payment*):** Countdown timer waktu pembayaran (misal: 2 jam). Jika kadaluarsa, pesanan otomatis berstatus *Expired* dan stok dikembalikan.
2. **Pembayaran Terverifikasi (*Paid*):** Gateway otomatis memberi tahu backend via webhook. Penjual menerima notifikasi pesanan baru.
3. **Sedang Dikemas (*Processing / Packed*):** Penjual menyiapkan barang dan melakukan *Request Pickup Biteship*.
4. **Dalam Pengiriman (*Shipped / In Transit*):** Nomor resi AWB aktif. Aplikasi menampilkan timeline pelacakan paket langsung dari server kurir ekspedisi.
5. **Pesanan Diterima & Selesai (*Delivered / Completed*):** Pembeli menekan tombol `[ Konfirmasi Terima Barang ]` atau selesai otomatis setelah 48 jam tiba.
6. **Ulasan Produk (*Review*):** Form ulasan bintang 1-5 dan unggah foto terbuka bagi pesanan yang telah berstatus *Completed*.

---

## 7. API Architecture & Data Model Mapping

### Mapping Entitas Backend Laravel COOCA

| Entitas Mobile | Model Backend COOCA | Tabel Database | Deskripsi Teknis |
| :--- | :--- | :--- | :--- |
| **Pembeli** | `GlobalCustomer` | `global_customers` | Akun pembeli global lintas seluruh toko UMKM COOCA. |
| **Buku Alamat** | `GlobalCustomerAddress`| `global_customer_addresses`| Alamat multi-lokasi lengkap dengan koordinat Lat/Long & Biteship Area ID. |
| **Keranjang** | `CustomerCart` | `customer_carts` | Terisolasi per pasangan `global_customer_id` & `business_id`. |
| **Item Keranjang** | `CustomerCartItem` | `customer_cart_items` | Produk, varian, add-on modifier, quantity, dan catatan. |
| **Pesanan** | `CommerceOrder` | `commerce_orders` | Pesanan toko resmi lengkap dengan token pelacakan & referensi gateway. |
| **Item Pesanan** | `CommerceOrderItem` | `commerce_order_items` | Snapshot harga beli, kuantitas, dan nama produk saat checkout dikunci. |
| **Ulasan** | `CommerceProductReview`| `commerce_product_reviews` | Ulasan pembeli terverifikasi (`is_verified_purchase = true`). |
| **Wishlist** | `CustomerWishlist` *(Baru)*| `customer_wishlists` | Tabel relasi produk yang difavoritkan oleh pembeli global. |

### Daftar Endpoint API Pembeli (`/api/v1/customer/*`)

```http
# ─── 1. Otentikasi Pembeli ───
POST /api/v1/customer/auth/register          (Daftar akun pembeli baru)
POST /api/v1/customer/auth/login             (Masuk akun pembeli, return token)
POST /api/v1/customer/auth/google            (Otentikasi Google SSO token exchange)
POST /api/v1/customer/auth/otp/send          (Kirim OTP WhatsApp)
POST /api/v1/customer/auth/otp/verify        (Verifikasi kode OTP WhatsApp)
POST /api/v1/customer/auth/logout            (Logout & hapus access token)

# ─── 2. Profil & Buku Alamat ───
GET  /api/v1/customer/profile                (Ambil profil pembeli)
PUT  /api/v1/customer/profile                (Update nama, email, foto profil)
GET  /api/v1/customer/addresses              (Daftar alamat tersimpan)
POST /api/v1/customer/addresses              (Tambah alamat baru + geocoding)
PUT  /api/v1/customer/addresses/{id}         (Ubah alamat)
POST /api/v1/customer/addresses/{id}/default (Jadikan alamat utama)
DELETE /api/v1/customer/addresses/{id}       (Hapus alamat)

# ─── 3. Katalog Marketplace Publik ───
GET  /api/v1/marketplace/home                (Feed beranda: banner, kategori, flash deals, rekomendasi)
GET  /api/v1/marketplace/products/search     (Pencarian produk, filter kategori, harga, rating)
GET  /api/v1/marketplace/products/{slug}     (Detail produk lengkap: varian, gambar, ulasan, estimasi ongkir)
GET  /api/v1/marketplace/stores/{slug}       (Profil toko: banner, informasi toko, seluruh katalog produk)
GET  /api/v1/marketplace/categories          (Daftar hierarki taksonomi kategori)

# ─── 4. Keranjang Belanja Multi-Toko ───
GET  /api/v1/customer/cart                   (Ambil seluruh keranjang yang dikelompokkan per toko)
POST /api/v1/customer/cart/{store}/add       (Tambah produk/varian ke keranjang toko tertentu)
PATCH /api/v1/customer/cart/{store}/item/{id}(Update quantity item)
DELETE /api/v1/customer/cart/{store}/item/{id}(Hapus item dari keranjang)

# ─── 5. Checkout & Logistik Ekspedisi ───
POST /api/v1/customer/checkout/rates         (Cek tarif ongkir kurir Biteship real-time)
POST /api/v1/customer/checkout/submit        (Kunci pesanan, kurangi stok, buat tagihan pembayaran)
POST /api/v1/customer/orders/{id}/pay        (Ambil instruksi bayar / kode QRIS TriPay)
POST /api/v1/customer/orders/{id}/proof      (Unggah foto bukti transfer manual)

# ─── 6. Manajemen Pesanan & Pelacakan ───
GET  /api/v1/customer/orders                 (Daftar pesanan dengan filter status)
GET  /api/v1/customer/orders/{id}            (Rincian pesanan lengkap)
GET  /api/v1/customer/orders/{id}/tracking   (Live tracking resi kurir AWB Biteship)
POST /api/v1/customer/orders/{id}/cancel     (Batalkan pesanan sebelum diproses penjual)
POST /api/v1/customer/orders/{id}/complete   (Konfirmasi penerimaan barang)
POST /api/v1/customer/orders/{id}/review     (Kirim ulasan rating 1-5 bintang & foto)

# ─── 7. Wishlist & Favorit ───
GET  /api/v1/customer/wishlist               (Daftar produk favorit)
POST /api/v1/customer/wishlist/toggle        (Tambah / hapus produk dari favorit)
```

---

## 8. Security & Anti-Fraud Architecture

1. **Anti-IDOR Strict Scoping:** Seluruh query pesanan, alamat, dan keranjang belanja **wajib** di-scope ke ID pembeli yang sedang terotentikasi (`$customer->id`). Akses pesanan milik orang lain langsung ditolak dengan kode `HTTP 403 Forbidden`.
2. **Pencegahan Manipulasi Harga (Anti-Tampering Price Shield):** Harga produk dan subtotal tidak pernah diterima dari aplikasi mobile. Saat checkout dipanggil, backend membaca ulang harga aktif dari database dan memeriksa promo yang berlaku.
3. **Pencegahan Rebutan Stok (*Stock Race Condition*):** Operasi checkout produk dengan stok terbatas dilindungi oleh **Database Pessimistic Locking (`lockForUpdate()`)** dan transaksi atomik database (`DB::transaction`). Jika stok habis di milidetik yang sama, checkout dibatalkan secara bersih dengan pesan ramah.
4. **Pencegahan Pembayaran Ganda (*Double-Payment Prevention*):** Tagihan TriPay dibuat dengan nomor referensi unik (`order_number`). Jika pembeli menekan tombol bayar berulang-ulang, sistem menggunakan referensi transaksi yang sama tanpa membuat tagihan ganda.
5. **Verifikasi Ulasan Sah:** Sistem memvalidasi bahwa ulasan hanya dapat diberikan untuk pesanan berstatus `completed` di mana pembeli benar-benar membeli produk tersebut (`is_verified_purchase = true`).

---

## 9. Performance & Image Delivery Architecture
* **Pengiriman Gambar WebP & Thumbnail:** Seluruh foto produk disimpan dan disajikan dalam format WebP terkompresi dengan ukuran bertingkat (*thumbnail 150px, preview 400px, full 1080px*).
* **Caching Katalog Cerdas:** Feed beranda dan kategori di-cache di Redis server dengan invalidasi otomatis via observer model saat merchant mengubah data produk.
* **Pagination Terukur:** Semua endpoint daftar produk dan ulasan menerapkan cursor-based atau page-based pagination maksimal 20 item per request untuk menjamin waktu respon sub-100ms.

---

## 10. QA Edge Cases & Failure Scenarios

| Skenario Pengujian | Kondisi Ekstrem / Edge Case | Ekspektasi Perilaku Sistem |
| :--- | :--- | :--- |
| **Perubahan Harga Saat Checkout** | Pedagang mengubah harga barang saat pembeli sedang berada di halaman checkout. | Backend menolak checkout, memberi notifikasi *"Harga produk telah diperbarui oleh penjual"*, dan memperbarui tampilan keranjang. |
| **Stok Habis Saat Transaksi** | Sisa stok 1 unit, dua pembeli menekan tombol checkout di detik yang sama. | Pembeli pertama berhasil; pembeli kedua menerima pesan *"Maaf, stok baru saja habis dibeli pelanggan lain"*. |
| **Kegagalan API Kurir Biteship** | Server Biteship timeout atau gangguan koneksi saat cek ongkir. | Aplikasi menyajikan opsi pengiriman alternatif manual atau pesan *"Layanan cek ongkir sedang padat, silakan coba 1 menit lagi"*. |
| **Koneksi Terputus Saat Bayar QRIS** | Pembeli sudah scan QRIS di bank, tetapi koneksi ponsel terputus sebelum kembali ke app. | Callback webhook TriPay di server tetap menerima konfirmasi pelunasan dari bank; saat aplikasi dibuka kembali, status pesanan otomatis *Sudah Dibayar*. |
| **Manipulasi ID Pesanan di URL/Payload** | Pembeli mencoba mengakses `GET /api/v1/customer/orders/{uuid_orang_lain}`. | Backend menolak dengan status `403 Forbidden` dan mencatat upaya pelanggaran ke audit log keamanan. |
