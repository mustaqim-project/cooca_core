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
1. **Pesan Antar / Kurir Pengiriman (*Delivery*):**  
   Dihitung berdasarkan aturan tarif ongkir (`commerce_shipping_rules`) per zona wilayah, kecamatan, atau flat rate.
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

---

## 4. Aturan Bisnis E-Commerce (Business Rules)

* **RULE-COMM-001 (Cart Isolation):** Satu sesi checkout hanya boleh memproses item dari satu tenant bisnis (`business_id`).
* **RULE-COMM-002 (Stock Reservation / Deduction):** Stok produk atau bahan baku resep BOM dipotong otomatis saat merchant mengubah status pesanan menjadi `paid` atau `processing`.
* **RULE-COMM-003 (Verified Contact Mandatory):** Pelanggan wajib memiliki nomor telepon atau email yang tervalidasi sebelum dapat menyelesaikan pesanan bernilai tinggi atau mengajukan reservasi.

---

## 5. Keterkaitan Lintas Modul

* **Ke Modul Inventory:** Memeriksa ketersediaan stok fisik secara langsung sebelum pembeli menyelesaikan checkout.
* **Ke Modul Finance:** Pesanan e-commerce yang diverifikasi lunas langsung memicu pencatatan uang masuk dan `AutoJournalService`.
* **Ke Modul WhatsApp:** Mengirimkan notifikasi invoice digital, nomor resi pengiriman, dan update status pesanan otomatis ke WhatsApp pembeli.
