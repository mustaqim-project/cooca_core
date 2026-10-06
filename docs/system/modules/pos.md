# Modul Point of Sale (POS) & Terminal Kasir

> **Status:** COMPLETE (Audited & Hardened)  
> **Domain Terkait:** `app/Domain/Pos/`, `app/Domain/Inventory/`, `app/Domain/Finance/`, `app/Domain/WhatsApp/`, `app/Domain/Printer/`  
> **Tabel Basis Data:** `pos_registers`, `pos_shifts`, `pos_cash_movements`, `pos_orders`, `pos_order_items`, `pos_order_item_modifiers`, `pos_payments`, `pos_tables`, `pos_table_sessions`, `pos_printers`, `modifiers`, `product_variants`, `product_channel_prices`, `product_bundle_items`, `store_edc_terminals`

---

## 1. Tujuan & Nilai Bisnis

Modul Point of Sale (POS) adalah pusat operasional transaksi harian seluruh lini bisnis merchant Cooca. Dirancang untuk kecepatan respons tinggi (*ultra-fast response*), keandalan tanpa henti (*zero downtime resilience*), serta kemudahan pengoperasian bagi kasir non-teknis melalui antarmuka layar sentuh (*touch-friendly*) berbasis **Bento Apple HIG v2.0**.

Sistem melayani **20 sektor industri bisnis di 6 klaster**, mengadaptasi antarmuka dan form secara sadar konteks (*Context-Aware UI*), serta menjaga integritas finansial dan stok melalui eksekusi atomik database.

Setiap transaksi di kasir POS secara otonom memicu:
1. **Pemotongan Stok Atomik:** Mengurangi saldo stok barang jadi fisik, mengeksekusi *Auto-BOM Deduction* untuk bahan mentah resep dapur, atau memotong komponen paket kombo secara rekursif (*Recursive Bundle Deduction*).
2. **Pencatatan Buku Kas & Multi-Ledger:** Memperbarui saldo berjalan akun kasir dan mencatat mutasi uang masuk secara real-time (`CashLedgerService`).
3. **Auto-Journaling Akuntansi:** Menghasilkan jurnal umum berimbang secara otomatis (Debit Kas/Bank/Piutang, Kredit Pendapatan Penjualan, Debit HPP, Kredit Persediaan).
4. **Distribusi Struk Terpadu:** Mencetak struk fisik thermal ESC/POS (58mm/80mm) dan mengirimkan nota digital resmi via WhatsApp Meta Cloud API.
5. **Sinkronisasi Multi-Harga Kanal:** Mengadaptasi harga saluran penjualan (Dine In, Takeaway, GoFood, GrabFood, ShopeeFood) dan mengalirkan antrean pesanan ke Kitchen Display System (KDS).

---

## 2. Fitur Utama Terminal Kasir & Ekosistem POS

### 2.1 Manajemen Register & Shift Kasir (Shift Control & Blind Cash Count)
* **Buka Shift (Shift Opening):** Kasir menginput modal awal kas di laci (*Float Cash / Opening Cash*) secara langsung atau menggunakan *Kalkulator Pecahan Uang Fisik* (Rp 100.000 s/d koin).
* **Kas Masuk & Keluar Laci (*Cash-In / Cash-Out*):** Kasir mencatat mutasi kas operasional mendadak (misal: beli es batu darurat, bayar galon air, atau tukar uang kembalian) disertai alasan wajib.
* **Tutup Shift & Rekonsiliasi (Shift Closing & Blind Drop):** Kasir menghitung uang fisik di laci tanpa melihat total sistem terlebih dahulu (*blind count*). Sistem secara independen menghitung selisih kas (*overage / shortage*) dan mencatatnya ke jurnal pembukuan serta mengirimkan alert ke Owner jika melampaui ambang batas toleransi.

### 2.2 Katalog Cepat & Pencarian Cerdas (Fast Touch Catalog)
* **Kategori Tab Dinamis:** Navigasi cepat antar kategori makanan, minuman, barang ritel, atau paket layanan.
* **Pencarian Real-Time & Barcode Scanner:** Mendukung pemindaian barcode fisik melalui barcode gun USB/Bluetooth atau pencarian instan nama/SKU/barcode.
* **Dukungan Varian & Modifier FnB:** Kasir dapat memilih varian (misal: *Ukuran Reguler / Large*, *Panas / Dingin*) serta tambahan modifier (*Topping Boba, Less Sugar, Extra Shot*) yang secara otomatis memodifikasi harga dan stok bahan.
* **Penandaan Visual Produk Kombo:** Produk paket kombo ditandai dengan lencana visual `[KOMBO (x Item)]` beraksen amber pada katalog kasir dan kartu produk.

### 2.3 Sesi Meja & Dine-In FnB (Table Sessions & Storefront Reservations)
* Manajemen denah meja, nomor meja, kapasitas tamu, dan status keterisian meja (*Kosong, Terisi, Menunggu Tagihan*).
* Pemisahan tagihan (*Split Bill*) atau penggabungan tagihan (*Merge Bill*).
* **Integrasi Reservasi Meja Online (Storefront Sync):** Menampilkan reservasi aktif hari ini (`today_reservations`) secara real-time pada header `/pos/tables`, kartu KPI reservasi harian, strip jadwal tamu bento, dan badge visual "Booked" pada nomor meja yang dipesan tamu melalui website toko.

### 2.4 Multi-Harga Saluran Penjualan & Online Delivery (F&B Channel Pricing)
* **Segmented Channel Selector Pill Bar:** Kasir dapat mengganti saluran penjualan secara instan melalui bar pilihan di header terminal (`Dine In`, `Takeaway`, `GoFood`, `GrabFood`, `ShopeeFood`).
* **Reaktivitas Harga Keranjang (Alpine.js):** Pergantian saluran langsung menghitung ulang harga katalog dan seluruh item yang ada di keranjang belanja kasir (`getProductPrice()`). Jika harga khusus saluran belum diset, sistem menggunakan harga dasar produk (*fallback*).
* **Nomor Pesanan Eksternal (External Order Ref):** Pilihan saluran online delivery memunculkan modal dialog untuk memasukkan nomor order dari aplikasi pengemudi online (misal: `GF-1092831`).
* **Lencana Kontras KDS (Kitchen Display System):** Pesanan yang masuk ke layar dapur menampilkan badge warna mencolok sesuai saluran untuk memudahkan pemisahan pengemasan makanan.
* **Format Struk Thermal Khusus:** Struk cetak thermal ESC/POS mencantumkan nama saluran dan nomor referensi pesanan eksternal.

### 2.5 Multi-Metode Pembayaran (Split Payment Support)
* **Tunai (Cash):** Kalkulasi kembalian otomatis dengan rekomendasi pecahan uang pas (`Rp 50.000`, `Rp 100.000`).
* **Non-Tunai (Digital Payment):** QRIS dinamis/statis, Kartu Debit/Kredit EDC, Transfer Bank.
* **Kasbon / Piutang Pelanggan (Pay Later):** Memungkinkan pelanggan terdaftar berbelanja secara kredit/bon dengan batas limit piutang.
* **Split Payment:** Pembayaran gabungan beberapa metode sekaligus (misal: Tunai + QRIS).

### 2.6 Mesin Cetak Struk Thermal & Hardware Management (ESC/POS Thermal Engine)
* Mendukung printer thermal standar industri ukuran **58mm** dan **80mm**.
* Konektivitas: Direct LAN/WiFi TCP/IP, Direct USB, Bluetooth Thermal Printer, atau Local POS Agent (Port 9898).
* **SSRF Guardrail:** Validasi alamat IP printer memblokir endpoint metadata cloud (`169.254.169.254`) dan loopback internal server (`127.0.0.1`).
* **Cash Drawer Safety Pulse:** Memicu pulsa pembukaan laci kas RJ11/RJ12 hanya pada transaksi tunai selesai atau No-Sale manual dengan PIN Supervisor.

### 2.8 Sistem Pelaporan & Analitik Penjualan POS (POS Reporting & Analytics Suite)
* **Single Source of Truth (`PosReportingService` & `PosReconciliationService`):**
  - Mengintegrasikan kalkulasi finansial hulu-ke-hilir: Gross Sales, Item Discounts, Voucher Promosi, Net Sales, Tax, Service Charge, HPP (*Cost of Goods Sold*), Laba Kotor, dan Margin %.
  - Mengurangi nilai retur penjualan secara otomatis pada kalkulasi Net Sales riil.
* **Rekonsiliasi Finansial 3-Arah (3-Way Reconciliation Engine):**
  - Validasi keseimbangan otomatis antara Tagihan Faktur Order ($\sum \text{Orders}$), Pembayaran Berhasil Gateway/Kas ($\sum \text{Payments}$), dan Mutasi Fisik Kas Shift Kasir ($\sum \text{Shift Cash Ledger}$).
  - Deteksi anomali fraud otomatis: Unsettled Payments, Overpaid Drift, Cash Register Shortage/Overage, Void Stolen Receipt.
* **Filter Bar Multi-Dimensi Anti-IDOR:**
  - Kombinasi filter dinamis berbasis preset tanggal (*Hari Ini, Kemarin, 7 Hari, 30 Hari, Bulan Ini, Bulan Lalu, Tahun Ini, Custom*), Cabang/Outlet, Kasir/User, Shift Kasir, Kategori Produk, Pelanggan, Saluran Jual, Tipe Order, dan Metode Pembayaran.
* **Bento Apple HIG 15-Tab Dynamic UI:**
  - 15 Sub-Modul Pelaporan Modular: *Ringkasan, Buku Transaksi, Produk & Menu, Kategori, Kasir & Staf, Cabang, Pembayaran, Audit Diskon, Retur & Refund, Audit Void/Fraud, Rekonsiliasi Kas Shift, Jam Sibuk (Heatmap SVG), Pelanggan, Saluran Jual, Margin & HPP*.
* **Slide-Over Modal Quick-View Detail Transaksi:**
  - Komponen laci detail transaksi interaktif (Alpine.js) untuk memeriksa rincian nota, breakdown harga, HPP, diskon, split payment, dan log cetak struk tanpa reload halaman.
* **Master 9-Worksheet Excel Export Engine (PhpSpreadsheet):**
  - Ekspor dokumen `.xlsx` 9-sheet profesional berstandar korporat yang 100% mematuhi seluruh kombinasi filter aktif dengan frozen panes dan formula baris.
* **Optimasi Performa Query Skala Besar (<150ms):**
  - Dilengkapi 11 composite indexes pada database untuk menjamin respons query agregasi tetap di bawah 150ms pada jutaan baris transaksi.

---

## 3. Aturan Bisnis POS (Business Rules)

* **RULE-POS-001 (Shift Must Be Active):** Transaksi kasir hanya dapat dilakukan jika register kasir berada dalam status shift terbuka (`status = open`).
* **RULE-POS-002 (Immutable Paid Orders):** Pesanan berstatus `paid` / `completed` bersifat permanen dan tidak dapat diedit langsung; koreksi wajib melalui prosedur *Return / Refund* dengan audit trail lengkap.
* **RULE-POS-003 (Negative Stock Handling):** Jika pengaturan bisnis `allow_negative_stock = false`, sistem menolak transaksi jika stok produk/bahan baku tidak mencukupi, disertai notifikasi ramah pengguna.
* **RULE-POS-004 (Storefront Table Booking Awareness):** POS Meja mengonsumsi reservasi aktif hari ini yang dibuat via Storefront. Meja yang telah di-booking ditandai dengan badge "Booked" dan detail jam kedatangan tamu agar staf kasir/waiter tidak menempatkan pelanggan walk-in di meja tersebut.
* **RULE-POS-005 (Channel Pricing Resolution):** Harga yang dikenakan pada kasir wajib mencerminkan harga saluran aktif (`product_channel_prices`). Jika rekaman harga untuk saluran tersebut tidak ditemukan, sistem wajib menggunakan harga dasar produk (`products.price`).
* **RULE-POS-006 (Bundle HPP Recording):** Untuk item bertipe paket kombo (`is_bundle = true`), field `total_hpp_cost` pada `pos_orders` diisi menggunakan nilai akumulatif `Product::getBundleHpp()`, dan pemotongan stok diteruskan secara rekursif ke seluruh item anak fisik maupun resep bahan baku anak.
* **RULE-POS-007 (Strict Blind Cash Count):** Pada antarmuka penutupan shift standar kasir, ekspektasi total kas sistem disembunyikan. Kasir wajib menginput hitungan fisik riil. Perhitungan selisih dilakukan murni di sisi server.
* **RULE-POS-008 (Server-Side Price Validation):** Validasi checkout pada backend wajib memverifikasi ulang harga dasar produk dan menolak manipulasi `unit_price` yang dikirim dari klien.
* **RULE-POS-009 (Public QR Order Customer Auto-Connect):** Setiap pemesanan meja mandiri publik wajib mencari atau membuat entitas `Customer` secara otomatis (`firstOrCreate`) menggunakan nomor telepon ternormalisasi dan menautkan `customer_id` ke `pos_orders`.
* **RULE-POS-010 (Phone Number Frontend Guardrails):** Input nomor telepon pada kanal publik wajib memiliki batas minimal 10 digit, auto-strip karakter non-numerik, format awalan Indonesia (`08...`/`628...`), serta font `text-[16px]` anti-zoom pada browser iOS.

---

## 4. Keterkaitan Lintas Modul

* **Ke Modul Commerce / Storefront:** Menerima sinkronisasi data reservasi meja hari ini dari `commerce_reservations` untuk ditampilkan di antarmuka meja kasir.
* **Ke Modul Inventory:** Mengurangi saldo stok secara atomik (*atomic stock decrement*) untuk produk fisik langsung, resep BOM, atau komponen paket kombo.
* **Ke Modul Finance:** Memperbarui saldo kas laci kasir dan memicu `AutoJournalService` dengan perhitungan laba kotor presisi berdasarkan HPP riil.
* **Ke Modul CRM:** Mengotomasi pendaftaran pelanggan meja ke tabel `customers`, menambah poin loyalitas pelanggan, dan mencatat riwayat pembelian pelanggan.
* **Ke Modul WhatsApp:** Mengirimkan pesan WhatsApp otomatis berisi ringkasan nota, saluran penjualan, dan tautan struk web resmi.
* **Dokumentasi Alur Terkait:**
  - [`docs/system/workflows/pos-sales-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/pos-sales-flow.md) - Alur Utama Penjualan Kasir POS & 20 Sektor Industri.
  - [`docs/system/workflows/pos-qr-order-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/pos-qr-order-flow.md) - Alur Public QR Table Order, Lifecycle Customer CRM & Guardrail UX.
  - [`docs/system/workflows/pos-channel-pricing-and-delivery-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/pos-channel-pricing-and-delivery-flow.md) - Alur Multi-Harga Saluran & Online Delivery.
  - [`docs/system/workflows/product-bundling-and-combo-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/product-bundling-and-combo-flow.md) - Alur Paket Kombo & Pemotongan Rekursif.

