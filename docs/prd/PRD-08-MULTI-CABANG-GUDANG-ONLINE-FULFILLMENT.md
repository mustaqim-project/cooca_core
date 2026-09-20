# PRD-08: Multi-Cabang & Multi-Gudang untuk Fulfillment Toko Online (Omnichannel)

**ID Dokumen:** `PRD-08-OMNICHANNEL-MULTI-BRANCH-FULFILLMENT`  
**Modul:** Multi-Lokasi, Manajemen Gudang & Pemenuhan Pesanan Online (Fulfillment)  
**Penanggung Jawab:** Principal Logistics & Omnichannel Architect  
**Status:** READY FOR IMPLEMENTATION  
**Target Pengguna:** Pembeli Toko Online, Manajer Operasional Cabang, Staf Gudang, Pemilik Bisnis  

---

## 1. Audit Sistem Existing (As-Is State)

### A. File & Komponen Terkait
* **Model Lokasi & Cabang:** [`app/Models/Location.php`](file:///c:/laragon/www/cooca_core/app/Models/Location.php)
* **Model Stok Persediaan:** [`app/Models/InventoryStock.php`](file:///c:/laragon/www/cooca_core/app/Models/InventoryStock.php) (mencatat kuantitas stok per `location_id`)
* **Pengaturan Toko Online:** [`app/Models/CommerceStoreSetting.php`](file:///c:/laragon/www/cooca_core/app/Models/CommerceStoreSetting.php) (kolom `origin_location_id`)
* **Integrasi Ekspedisi:** [`app/Domain/Shipping/BiteshipService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Shipping/BiteshipService.php)

### B. Temuan & Batasan Sistem Existing
1. **Pondasi Multi-Lokasi Backend Sudah Kuat:**  
   COOCA sudah memiliki tabel `locations` dengan tipe `outlet`, `warehouse`, dan `central_kitchen`, serta tabel `inventory_stocks` yang memantau kuantitas stok independen per lokasi.
2. **Keterbatasan Toko Online Saat Ini:**
   - Toko online saat ini hanya terkunci pada **satu titik asal pengiriman tunggal** (`origin_location_id`).
   - Jika pemilik bisnis memiliki 5 cabang (cth: Jakarta, Bandung, Surabaya, Medan, Makassar), pembeli di Surabaya tetap dihitung ongkirnya dari Jakarta! Hal ini membuat ongkir sangat mahal dan barang lama sampai.
   - Tidak ada fitur bagi pembeli untuk memilih cabang pengambilan sendiri di tempat (*Self-Pickup / Click & Collect*).
   - Belum ada sakelar per lokasi untuk menentukan cabang mana yang melayani penjualan online dan cabang mana yang murni hanya untuk gudang penyimpanan bahan mentah.

---

## 2. Perubahan & Penambahan Sistem (To-Be State)

### A. Penambahan Kolom Database pada Tabel `locations`
```sql
ALTER TABLE locations 
    ADD COLUMN is_online_fulfillment BOOLEAN NOT NULL DEFAULT FALSE AFTER is_active,
    ADD COLUMN allow_storefront_pickup BOOLEAN NOT NULL DEFAULT FALSE AFTER is_online_fulfillment,
    ADD COLUMN biteship_origin_id VARCHAR(100) NULL AFTER allow_storefront_pickup,
    ADD COLUMN postal_code VARCHAR(10) NULL AFTER biteship_origin_id,
    ADD COLUMN lead_time_pickup_minutes INT UNSIGNED NOT NULL DEFAULT 60 AFTER postal_code;
```

---

### B. Dua Skenario Utama Pemenuhan Pesanan Online

#### 1. Skenario A: Pengiriman Kurir Cerdas (Smart Nearest-Branch Routing)
* **Alur Kerja:**
  1. Saat checkout di toko online, pembeli memasukkan alamat tujuan atau titik koordinat GPS.
  2. **Smart Routing Engine:**
     - Mengambil seluruh cabang/gudang yang `is_online_fulfillment = true`.
     - Memfilter cabang yang memiliki persediaan stok mencukupi (`inventory_stocks.quantity >= ordered_quantity`).
     - Menghitung jarak Haversine antara lokasi pembeli dengan masing-masing cabang yang lolos filter stok.
     - Memilih cabang dengan **jarak terdekat**.
  3. Sistem menghitung ongkir Biteship dari alamat cabang terdekat tersebut.
  4. **Dampak Nyata:**
     - Ongkir pembeli menjadi sangat murah (bisa memanfaatkan kurir instan 1–2 jam GoSend/GrabExpress).
     - Barang tiba di hari yang sama.
     - Mengeliminasi biaya logistik antar-kota yang membebani pembeli.

#### 2. Skenario B: Ambil Sendiri di Toko (Click & Collect / Self-Pickup)
* **Alur Kerja:**
  1. Pembeli memilih opsi pengiriman: **"Ambil Sendiri di Toko (Bebas Ongkir)"**.
  2. Sistem menampilkan daftar cabang yang mengaktifkan `allow_storefront_pickup = true`.
  3. Menampilkan status ketersediaan stok real-time di masing-masing cabang:
     - *Cabang Sudirman (Stok Tersedia: 14 pcs) $\rightarrow$ Siap diambil dalam 1 jam.*
     - *Cabang BSD (Stok Tersedia: 5 pcs) $\rightarrow$ Siap diambil dalam 1 jam.*
     - *Cabang Kelapa Gading (Stok Habis) $\rightarrow$ Pilihan dinonaktifkan otomatis.*
  4. Pembeli menyelesaikan checkout tanpa membayar ongkir, dan sistem mengirimkan QR Code bukti pengambilan ke WhatsApp pembeli.

---

### C. Sinkronisasi Stok Multi-Gudang & Pencegahan Overselling
* Begitu pesanan online dibayar:
  1. Sistem langsung mengurangi kolom `quantity` dan menambah `reserved_quantity` pada tabel `inventory_stocks` **khusus pada `location_id` yang menjadi sumber pemenuhan pesanan**.
  2. Kasir POS di toko fisik cabang tersebut langsung menerima pembaruan stok secara *real-time*.
  3. Hal ini mencegah *overselling* (kasir offline tidak akan menjual fisik barang yang sudah dibayar oleh pembeli online).

---

## 3. Diagram Alur Kerja Fulfillment Omnichannel

```
[ PEMBELI CHECKOUT DI TOKO ONLINE ]
                 │
        ┌────────┴────────┐
        ▼                 ▼
[ PILIH KURIR ]    [ PILIH AMBIL DI TOKO ]
        │                 │
        ▼                 ▼
[ SMART ROUTING ]  [ TAMPILKAN CABANG DENGAN STOK ]
Cari cabang terdekat (Pembeli memilih cabang tujuan)
dengan stok cukup
        │                 │
        └────────┬────────┘
                 ▼
[ PESANAN TERKONFIRMASI ]
                 │
                 ▼
[ POTONG STOK DI inventory_stocks PADA LOKASI TERKAIT ]
                 │
                 ▼
[ KASIR / STAF CABANG MENERIMA NOTIFIKASI PICKUP/PACKING ]
```

---

## 4. Kriteria Keberhasilan (Definition of Done)
1. Pemilik bisnis dapat mengaktifkan/menonaktifkan sakelar `is_online_fulfillment` dan `allow_storefront_pickup` per cabang.
2. Checkout online dengan opsi kurir otomatis memilih cabang terdekat yang memiliki stok produk tersebut.
3. Checkout online dengan opsi ambil di toko menampilkan status stok cabang secara akurat dan tidak dapat dipilih jika stok cabang tersebut kosong.
4. Stok terpotong presisi pada cabang pengirim tanpa mempengaruhi catatan stok cabang lainnya.
