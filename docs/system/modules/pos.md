# Modul Point of Sale (POS) & Terminal Kasir

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Domain/Pos/`, `app/Domain/Inventory/`, `app/Domain/Finance/`, `app/Domain/WhatsApp/`  
> **Tabel Basis Data:** `pos_registers`, `pos_shifts`, `pos_orders`, `pos_order_items`, `pos_payments`, `pos_tables`, `pos_sessions`, `pos_security_pins`, `modifiers`, `product_variants`, `product_channel_prices`, `product_bundle_items`

---

## 1. Tujuan & Nilai Bisnis

Modul Point of Sale (POS) adalah ujung tombak transaksi kasir harian Cooca. Dirancang untuk kecepatan tinggi (*ultra-fast response*), keandalan tanpa henti, dan kemudahan pengoperasian bagi kasir non-teknis melalui antarmuka layar sentuh (*touch-friendly*) berbasis **Apple HIG v2.0 Bento UI**.

Setiap transaksi di kasir POS secara otonom memicu:
1. Pemotongan stok produk langsung, pemotongan bahan baku mentah resep BOM (*Auto-BOM Deduction*), atau pemotongan rekursif seluruh komponen paket kombo (*Recursive Bundle Deduction*).
2. Pencatatan uang masuk pada akun kasir dan mutasi buku besar (*Real-Time Cash Ledger*).
3. Pembuatan jurnal akuntansi berimbang secara otomatis (*Auto-Journaling*).
4. Pengiriman struk digital resmi via WhatsApp ke pelanggan (*Auto-WhatsApp Receipt*).
5. Sinkronisasi multi-harga kanal penjualan (Dine In, Takeaway, GoFood, GrabFood, ShopeeFood) dan penandaan antrean dapur (KDS).

---

## 2. Fitur Utama Terminal Kasir

### 2.1 Manajemen Register & Shift Kasir (Register & Shift Control)
* **Buka Shift (Shift Opening):** Kasir menginput modal awal kas di laci (*Float Cash / Opening Cash*) sebelum memulai transaksi.
* **Kas Masuk & Keluar Laci (*Cash-In / Cash-Out*):** Kasir mencatat mutasi kas mendadak di luar penjualan (contoh: membeli es batu darurat, bayar galon air, atau tukar uang kembalian).
* **Tutup Shift & Rekonsiliasi (Shift Closing & Blind Drop):** Kasir menghitung uang fisik di laci tanpa melihat total sistem terlebih dahulu (*blind count*). Sistem secara otomatis menghitung selisih kas (*overage / shortage*) dan mencatatnya ke jurnal pembukuan.

### 2.2 Katalog Cepat & Pencarian Cerdas (Fast Touch Catalog)
* **Kategori Tab Dinamis:** Navigasi cepat antar kategori makanan, minuman, barang ritel, atau jasa.
* **Pencarian Real-Time & Barcode Scanner:** Mendukung pemindaian barcode fisik melalui barcode gun USB/Bluetooth atau pencarian instan nama/SKU.
* **Dukungan Varian & Modifier FnB:** Kasir dapat memilih varian (misal: *Ukuran Reguler / Large*, *Panas / Dingin*) serta tambahan modifier (*Topping Boba, Less Sugar, Extra Shot*) yang secara otomatis memodifikasi harga dan stok bahan.
* **Penandaan Visual Produk Kombo:** Produk paket kombo ditandai dengan lencana visual `[KOMBO (x Item)]` beraksen amber pada katalog kasir dan kartu produk.

### 2.3 Sesi Meja & Dine-In FnB (Table Sessions & Storefront Reservations)
* Manajemen denah meja, nomor meja, status keterisian meja (*Kosong, Terisi, Menunggu Tagihan*).
* Pemisahan tagihan (*Split Bill*) atau penggabungan tagihan (*Merge Bill*).
* **Integrasi Reservasi Meja Online (Storefront Sync):** Menampilkan reservasi aktif hari ini (`today_reservations`) secara real-time pada header `/pos/tables`, kartu KPI reservasi harian, strip jadwal tamu bento, dan badge visual "Booked" pada nomor meja yang dipesan tamu melalui website toko.

### 2.4 Multi-Harga Saluran Penjualan & Online Delivery (F&B Channel Pricing)
* **Segmented Channel Selector Pill Bar:** Kasir dapat mengganti saluran penjualan secara instan melalui bar pilihan di header terminal (`Dine In`, `Takeaway`, `GoFood`, `GrabFood`, `ShopeeFood`).
* **Reaktivitas Harga Keranjang (Alpine.js):** Pergantian saluran langsung menghitung ulang harga katalog dan seluruh item yang ada di keranjang belanja kasir (`getProductPrice()`). Jika harga khusus saluran belum diset, sistem menggunakan harga dasar produk (*fallback*).
* **Nomor Pesanan Eksternal (External Order Ref):** Pilihan saluran online delivery memunculkan modal dialog untuk memasukkan nomor order dari aplikasi pengemudi online (misal: `GF-1092831`).
* **Lencana Kontras KDS (Kitchen Display System):** Pesanan yang masuk ke layar dapur menampilkan badge warna mencolok sesuai saluran (GoFood hijau, GrabFood hijau toska, ShopeeFood oranye, Takeaway oranye) untuk memudahkan pemisahan pengemasan makanan.
* **Format Struk Thermal Khusus:** Struk cetak thermal ESC/POS (58mm/80mm) dan gambar struk digital mencantumkan nama saluran dan nomor referensi pesanan eksternal.

### 2.5 Multi-Metode Pembayaran (Split Payment Support)
* **Tunai (Cash):** Kalkulasi kembalian otomatis dengan rekomendasi pecahan uang pas (`Rp 50.000`, `Rp 100.000`).
* **Non-Tunai (Digital Payment):** QRIS dinamis/statis, Kartu Debit/Kredit EDC, Transfer Bank.
* **Kasbon / Piutang Pelanggan (Pay Later):** Memungkinkan pelanggan terdaftar berbelanja secara kredit/bon dengan batas limit piutang.

### 2.6 Mesin Cetak Struk Thermal (ESC/POS Thermal Engine)
* Mendukung printer thermal standar industri ukuran **58mm** dan **80mm**.
* Konektivitas: Direct USB, Bluetooth Thermal Printer, atau TCP/IP Network Printer.
* Format struk resmi mencantumkan logo bisnis, saluran penjualan, nomor pesanan aplikasi luar, rincian diskon/pajak, catatan meja, dan tautan struk digital.

### 2.7 Otorisasi Supervisor Kasir (Security PIN Shield)
* Melindungi tindakan kasir berisiko tinggi dari kecurangan/penipuan:
  - Pembatalan transaksi (*Void Order*).
  - Pengembalian dana (*Refund*).
  - Pembukaan laci kas secara manual (*No-Sale / Open Drawer*).
* Wajib memasukkan **PIN Supervisor 6 digit** yang terenkripsi Bcrypt dan dibatasi rate limit (*throttle: 5, 1 menit*).

---

## 3. Aturan Bisnis POS (Business Rules)

* **RULE-POS-001 (Shift Must Be Active):** Transaksi kasir hanya dapat dilakukan jika register kasir berada dalam status shift terbuka (`status = open`).
* **RULE-POS-002 (Immutable Paid Orders):** Pesanan berstatus `paid` bersifat permanen dan tidak dapat diedit langsung; koreksi wajib melalui prosedur *Return / Refund* dengan audit trail lengkap.
* **RULE-POS-003 (Negative Stock Handling):** Jika pengaturan bisnis `allow_negative_stock = false`, sistem menolak transaksi jika stok produk/bahan baku tidak mencukupi, disertai notifikasi ramah pengguna.
* **RULE-POS-004 (Storefront Table Booking Awareness):** POS Meja mengonsumsi reservasi aktif hari ini yang dibuat via Storefront. Meja yang telah di-booking ditandai dengan badge "Booked" dan detail jam kedatangan tamu agar staf kasir/waiter tidak menempatkan pelanggan walk-in di meja tersebut.
* **RULE-POS-005 (Channel Pricing Resolution):** Harga yang dikenakan pada kasir wajib mencerminkan harga saluran aktif (`product_channel_prices`). Jika rekaman harga untuk saluran tersebut tidak ditemukan, sistem wajib menggunakan harga dasar produk (`products.price`).
* **RULE-POS-006 (Bundle HPP Recording):** Untuk item bertipe paket kombo (`is_bundle = true`), field `total_hpp_cost` pada `pos_orders` diisi menggunakan nilai akumulatif `Product::getBundleHpp()`, dan pemotongan stok diteruskan secara rekursif ke seluruh item anak fisik maupun resep bahan baku anak.

---

## 4. Keterkaitan Lintas Modul

* **Ke Modul Commerce / Storefront:** Menerima sinkronisasi data reservasi meja hari ini dari `commerce_reservations` untuk ditampilkan di antarmuka meja kasir.
* **Ke Modul Inventory:** Mengurangi saldo stok secara atomik (*atomic stock decrement*) untuk produk fisik langsung, resep BOM, atau komponen paket kombo.
* **Ke Modul Finance:** Memperbarui saldo kas laci kasir dan memicu `AutoJournalService` dengan perhitungan laba kotor presisi berdasarkan HPP riil.
* **Ke Modul CRM:** Menambahkan poin loyalitas pelanggan dan mencatat riwayat pembelian pelanggan.
* **Ke Modul WhatsApp:** Mengirimkan pesan WhatsApp otomatis berisi ringkasan nota, saluran penjualan, dan tautan struk web resmi.
* **Dokumentasi Alur Terkait:**
  - [`docs/system/workflows/pos-sales-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/pos-sales-flow.md) - Alur Utama Penjualan Kasir POS.
  - [`docs/system/workflows/pos-channel-pricing-and-delivery-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/pos-channel-pricing-and-delivery-flow.md) - Alur Multi-Harga Saluran & Online Delivery.
  - [`docs/system/workflows/product-bundling-and-combo-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/product-bundling-and-combo-flow.md) - Alur Paket Kombo & Pemotongan Rekursif.
