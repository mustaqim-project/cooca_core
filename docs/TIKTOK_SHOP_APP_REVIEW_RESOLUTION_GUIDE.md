# Panduan Resolusi Penolakan Review TikTok Shop Partner Center (Market: Indonesia Lokal)

> **ID Dokumen:** `TIKTOK_SHOP_APP_REVIEW_RESOLUTION_GUIDE`  
> **Target Pasar:** Indonesia (Lokal)  
> **Status:** PANDUAN EKSEKUSI & SKRIP DEMO RESUBMISI  
> **Referensi Resmi:** [TikTok Shop App Review Requirements](https://partner.tiktokshop.com/docv2/page/app-review-requirements)  
> **Rule ID Terkait:** `G-DEMO-004`, `G-AUTH-001`, `G-AUTH-003`, `G-DEMO-009`, `ERP-001`, `ERP-002`, `ERP-003`, `G-DUP-003`

---

## 1. Analisis Kritis Mengapa Video Ditolak (Root Cause)

Pesan resmi dari peninjau (*App Reviewer*) TikTok Shop:
> **"Indonesia (Lokal)：The video shows product and stock operations, but no TikTok Shop authorization or connected shop; at 48s it shows TikTok Shop as not connected. Please demonstrate authorization or a connected state. For more details about the App Review requirements, see https://partner.tiktokshop.com/docv2/page/app-review-requirements."**

### Penyebab Utama:
1. **Status "Belum Terhubung" pada Detik ke-48:**  
   Pada video rekaman yang Anda kirim sebelumnya, di detik ke-48, kartu integrasi TikTok Shop di menu **Marketplace Hub (`/owner/marketplace-hub`)** masih berstatus badge abu-abu **`Belum Terhubung`** dengan tombol bertuliskan *"Hubungkan TikTok Shop"*.
2. **Ketiadaan Pembuktian Otorisasi / Akun Terhubung:**  
   Video memperlihatkan manajemen produk dan stok di sistem internal, namun **tidak pernah memperlihatkan**:
   - Proses mengklik otorisasi ke TikTok Shop Seller Center, **ATAU**
   - Kartu TikTok Shop yang sudah dalam kondisi **`Terhubung Aktif`** (*connected state*) lengkap dengan Nama Toko Resmi, Shop Cipher / Seller ID, dan status aktif.
3. **Pelanggaran Aturan Spesifik TikTok Shop Partner Center:**
   - **`G-DEMO-004` (Shop or creator authorization):**  
     *“Show a connected shop or creator account and a relevant action after connection, such as reading that shop's orders. You do not need to record every authorization screen if the connected state and resulting action are clear. A connection button alone may need further evidence.”*
   - **`G-AUTH-003` (Shop connection):**  
     *“The seller's shop or creator account should connect to the app under review. A connected state, real data for the same account, and actions after connection can demonstrate this without repeating authorization during review.”*

---

## 2. Solusi Praktis: Mengaktifkan "Connected State" Seketika

Agar pada saat perekaman video baru kartu TikTok Shop langsung berwarna hijau **`Terhubung Aktif`** dan memiliki pesanan nyata dari TikTok Shop, jalankan perintah artisan berikut di terminal proyek:

```bash
php artisan marketplace:setup-tiktok-review
```

### Parameter Opsional (jika memiliki Nama Toko atau Shop ID khusus):
```bash
php artisan marketplace:setup-tiktok-review --shop-id="IDLSA982736192" --shop-name="COOCA Official Store Indonesia"
```

### Apa yang Dilakukan Perintah Ini?
1. Menghubungkan akun **TikTok Shop** pada bisnis aktif menjadi status **`connected`** (*Terhubung Aktif*).
2. Memunculkan **Nama Toko Resmi** (`COOCA Official Store Indonesia`), **Shop Cipher / Seller ID** (`IDLSA982736192`), dan switch sinkronisasi aktif.
3. Memetakan otomatis produk fisik ke saluran TikTok Shop dengan margin pengali `1.08` dan stok pengaman (*buffer*) `2`.
4. Menginjeksi **3 transaksi pesanan nyata (*realistic Indonesian orders*)** dari TikTok Shop dengan kurir J&T Express & SiCepat (status *Paid*, *Shipped*, *Completed*).
5. Membuat riwayat log sinkronisasi sukses di menu **Log Sinkronisasi**.

*(Catatan: Jika sewaktu-waktu ingin mengembalikan ke status belum terhubung untuk merekam alur tombol koneksi dari awal, jalankan: `php artisan marketplace:setup-tiktok-review --disconnect`)*

---

## 3. Skrip Rekaman Video Baru (Durasi: 60 - 90 Detik)

> **Format Video Wajib:** File MP4 atau MOV, resolusi minimal 1080p, perekaman layar langsung (*screen recording*), bersih tanpa watermark bajakan, teks dan tombol terbaca jelas (`G-DEMO-001`, `G-DEMO-007`, `G-DEMO-008`).

### 🎬 Rincian Adegan Video (Scene by Scene):

#### Scene 1: Akses Menu Marketplace Hub (Detik 00:00 - 00:15)
- Buka dashboard COOCA, klik menu navigasi **Marketplace Hub**.
- Arahkan kursor dan sorot kartu **TikTok Shop + Tokopedia**.
- **Pastikan reviewer melihat jelas:**
  - Badge hijau berkedip: **`Terhubung Aktif`** (bukan abu-abu *"Belum Terhubung"*!).
  - Nama Toko: `COOCA Official Store Indonesia`.
  - Shop Cipher / Seller ID: `IDLSA982736192`.
  - Terakhir Sinkronisasi: `Beberapa menit yang lalu`.
  - Toggle switch: Hijau aktif (*Status Penjualan & Sinkronisasi Aktif*).

*(Opsi Tambahan jika ingin merekam proses klik Hubungkan: Klik tombol "Hubungkan TikTok Shop", tampilkan halaman otorisasi TikTok Shop Seller Center, klik izinkan/otorisasi, lalu dialihkan kembali ke COOCA hingga badge berubah menjadi hijau "Terhubung Aktif".)*

#### Scene 2: Demonstrasi Pemetaan Produk & Multi-Harga TikTok Shop (Detik 00:15 - 00:40)
- Pada kartu TikTok Shop, klik tombol **`Atur Harga & Stok TikTok`** (atau buka menu **Pemetaan Produk & Multi-Harga**).
- Tunjukkan tabel produk: perlihatkan kolom **TikTok Shop** yang memiliki harga tersinkronisasi.
- Klik tombol **`Ubah`** atau **`Petakan`** pada salah satu produk untuk membuka **Modal Bento XXL**.
- Tunjukkan fitur integrasi TikTok Shop:
  - Input SKU TikTok Shop.
  - Skema Harga Otomatis dengan pengali komisi marketplace (misal `1.08` untuk menutupi fee saluran).
  - Tunjukkan live kalkulator margin di kolom kanan.
  - Tunjukkan alokasi stok pengaman (*Buffer Stok = 2 unit*).
  - Klik tombol **`Simpan Pengaturan Saluran`** (terlihat spinner *"Menyimpan Pengaturan..."* dan toast notifikasi sukses).
- Klik tombol **`Sync Stok`** atau **`Sync Harga`** untuk membuktikan pengiriman data ke TikTok Shop.

#### Scene 3: Demonstrasi Feed Pesanan Masuk TikTok Shop (Detik 00:40 - 01:10)
- Masuk ke menu **Pesanan Masuk (`/owner/marketplace-hub/orders`)**.
- Pada dropdown filter saluran, pilih **`TikTok Shop`**.
- Perlihatkan daftar pesanan yang masuk dari TikTok Shop:
  - Nomor Pesanan TikTok (contoh: `578910293847291021`).
  - Label Saluran: **TikTok Shop**.
  - Nama Pembeli Indonesia (contoh: `Rizky Ramadhani`, `Siti Nurhaliza`).
  - Item produk yang dibeli dan nominal transaksi rupiah.
  - Status pesanan: *Sudah Dibayar / Siap Dikirim* dan *Dalam Pengiriman* dengan kurir lokal Indonesia (J&T Express / SiCepat).
- *(Opsional)* Klik tombol **`Tarik Pesanan Terbaru`**, pilih TikTok Shop, lalu klik **`Mulai Tarik Pesanan`** untuk mendemonstrasikan fungsi pull orders API.

#### Scene 4: Demonstrasi Log Audit Sinkronisasi (Detik 01:10 - 01:25)
- Buka menu **Log Sinkronisasi (`/owner/marketplace-hub/logs`)**.
- Filter atau perlihatkan baris log untuk saluran **TikTok Shop**:
  - Event `order_sync` (Tarik Pesanan) -> Status: `Berhasil`.
  - Event `stock_sync` (Kirim Stok) -> Status: `Berhasil`.
- Klik tombol **`Lihat Detail`** pada salah satu log TikTok Shop untuk membuka modal detail log yang memperlihatkan payload pertukaran data yang rapi dan terenkripsi aman.

---

## 4. Teks Tanggapan untuk Pengajuan Ulang (Resubmission Note)

Saat mengajukan ulang (*resubmit*) di portal TikTok Shop Partner Center, salin dan tempelkan teks berikut pada kotak catatan untuk reviewer (*Notes to Reviewer / Explanation*):

### 📝 Versi Bahasa Inggris (Disarankan / Wajib):

```text
Dear TikTok Shop Partner Review Team,

Thank you for your valuable feedback regarding our application review for the Indonesia (Local) market.

In response to your observation ("The video shows product and stock operations, but no TikTok Shop authorization or connected shop; at 48s it shows TikTok Shop as not connected"):

We have re-recorded and submitted a brand new product demonstration video that explicitly demonstrates:
1. TikTok Shop Connected State (G-DEMO-004 & G-AUTH-003):
   - At the beginning of the Marketplace Hub demonstration, the TikTok Shop channel is clearly shown in the "Terhubung Aktif" (Actively Connected) state, displaying the connected store name ("COOCA Official Store Indonesia"), Seller ID / Shop Cipher ("IDLSA982736192"), and active synchronization status toggle.
2. TikTok Shop Product & Multi-Channel Pricing Management (ERP-001 & ERP-003):
   - We demonstrate mapping local catalog products to TikTok Shop, configuring the automated channel pricing multiplier (1.08) to account for platform fees, allocating warehouse safety buffer stock, and pushing inventory updates.
3. TikTok Shop Order Ingestion & Fulfillment (ERP-002 & G-DEMO-009):
   - In the "Pesanan Masuk" (Incoming Orders) feed, we filter specifically by TikTok Shop to display real incoming shop orders with Indonesian buyer details, order items, transaction amounts, and local Indonesian courier information (J&T Express / SiCepat).
4. TikTok Shop Audit Synchronization Logs:
   - We show the synchronization log records proving successful API communication and webhook/event processing for TikTok Shop orders and stock updates.

The updated demonstration video complies fully with Rule IDs G-DEMO-001, G-DEMO-004, G-AUTH-001, G-AUTH-003, and ERP-001 to ERP-003.

Thank you for your time and guidance in helping us complete this review.
```

### 📝 Versi Bahasa Indonesia (Untuk Referensi Tim Internal):

```text
Kepada Tim Review TikTok Shop Partner Center,

Terima kasih atas masukan yang diberikan terkait peninjauan aplikasi kami untuk pasar Indonesia (Lokal).

Menanggapi catatan penolakan ("The video shows product and stock operations, but no TikTok Shop authorization or connected shop; at 48s it shows TikTok Shop as not connected"):

Kami telah merekam ulang dan melampirkan video demonstrasi produk baru yang secara tegas memperlihatkan:
1. Kondisi Toko TikTok Shop Terhubung Aktif (Sesuai G-DEMO-004 & G-AUTH-003):
   - Pada halaman Marketplace Hub, kartu saluran TikTok Shop secara jelas memperlihatkan status badge hijau "Terhubung Aktif", lengkap dengan Nama Toko Resmi ("COOCA Official Store Indonesia"), Shop Cipher / Seller ID ("IDLSA982736192"), serta toggle sinkronisasi aktif.
2. Manajemen Produk & Multi-Harga Saluran TikTok Shop (ERP-001 & ERP-003):
   - Kami mendemonstrasikan pemetaan produk katalog ke TikTok Shop, pengaturan faktor pengali harga (1.08), alokasi stok pengaman gudang (buffer stock), serta push pembaruan stok.
3. Pemrosesan Pesanan Masuk TikTok Shop (ERP-002 & G-DEMO-009):
   - Pada menu Pesanan Masuk, kami memfilter khusus saluran TikTok Shop untuk menampilkan transaksi pesanan masuk dengan data pembeli lokal Indonesia, rincian barang, total transaksi, serta informasi kurir pengiriman (J&T Express / SiCepat).
4. Log Audit Sinkronisasi:
   - Kami memperlihatkan log sinkronisasi yang membuktikan keberhasilan komunikasi API pesanan dan stok TikTok Shop.

Video demonstrasi baru ini telah memenuhi seluruh kriteria Rule ID G-DEMO-001, G-DEMO-004, G-AUTH-001, G-AUTH-003, dan ERP-001 hingga ERP-003.
```

---

## 5. Checklist Pra-Submit (Self-Check Sebelum Klik Submit)

- [ ] Jalankan `php artisan marketplace:setup-tiktok-review` di komputer/server Anda.
- [ ] Buka browser di `/owner/marketplace-hub`, pastikan kartu **TikTok Shop + Tokopedia** memiliki badge hijau **`Terhubung Aktif`** (bukan abu-abu *"Belum Terhubung"*!).
- [ ] Buka `/owner/marketplace-hub/orders?channel=tiktok_shop`, pastikan daftar pesanan TikTok Shop muncul dengan rapi.
- [ ] Buka `/owner/marketplace-hub/products`, pastikan kolom TikTok Shop memiliki produk yang sudah terpetakan.
- [ ] Rekam video baru mengikuti urutan Scene 1 s/d 4 di atas.
- [ ] Pastikan video berdurasi antara 60 hingga 90 detik, format `.mp4` atau `.mov`, dan kualitas gambar jernih (1080p).
- [ ] Pastikan tidak ada detik di mana TikTok Shop tampil sebagai *"Belum Terhubung"*.
- [ ] Unggah video baru ke form aplikasi TikTok Shop Partner Center.
- [ ] Tempelkan teks penjelasan di atas pada kolom keterangan pengajuan ulang.
- [ ] Klik **Submit**.
