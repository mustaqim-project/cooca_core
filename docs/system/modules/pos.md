# Modul Point of Sale (POS) & Terminal Kasir

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Domain/Pos/`, `app/Domain/Inventory/`, `app/Domain/Finance/`, `app/Domain/WhatsApp/`  
> **Tabel Basis Data:** `pos_registers`, `pos_shifts`, `pos_orders`, `pos_order_items`, `pos_payments`, `pos_tables`, `pos_sessions`, `pos_security_pins`, `modifiers`, `product_variants`

---

## 1. Tujuan & Nilai Bisnis

Modul Point of Sale (POS) adalah ujung tombak transaksi kasir harian Cooca. Dirancang untuk kecepatan tinggi (*ultra-fast response*), keandalan tanpa henti, dan kemudahan pengoperasian bagi kasir non-teknis melalui antarmuka layar sentuh (*touch-friendly*) berbasis **Apple HIG v2.0 Bento UI**.

Setiap transaksi di kasir POS secara otonom memicu:
1. Pemotongan stok produk langsung atau pemotongan bahan baku mentah berdasarkan resep BOM (*Auto-BOM Deduction*).
2. Pencatatan uang masuk pada akun kasir dan mutasi buku besar (*Real-Time Cash Ledger*).
3. Pembuatan jurnal akuntansi berimbang secara otomatis (*Auto-Journaling*).
4. Pengiriman struk digital resmi via WhatsApp ke pelanggan (*Auto-WhatsApp Receipt*).

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

### 2.3 Sesi Meja & Dine-In FnB (Table Sessions)
* Manajemen denah meja, nomor meja, status keterisian meja (*Kosong, Terisi, Menunggu Tagihan*).
* Pemisahan tagihan (*Split Bill*) atau penggabungan tagihan (*Merge Bill*).

### 2.4 Multi-Metode Pembayaran (Split Payment Support)
* **Tunai (Cash):** Kalkulasi kembalian otomatis dengan rekomendasi pecahan uang pas (`Rp 50.000`, `Rp 100.000`).
* **Non-Tunai (Digital Payment):** QRIS dinamis/statis, Kartu Debit/Kredit EDC, Transfer Bank.
* **Kasbon / Piutang Pelanggan (Pay Later):** Memungkinkan pelanggan terdaftar berbelanja secara kredit/bon dengan batas limit piutang.

### 2.5 Mesin Cetak Struk Thermal (ESC/POS Thermal Engine)
* Mendukung printer thermal standar industri ukuran **58mm** dan **80mm**.
* Konektivitas: Direct USB, Bluetooth Thermal Printer, atau TCP/IP Network Printer.
* Format struk resmi mencantumkan logo bisnis, detail pesanan, rincian diskon/pajak, catatan meja, dan link struk digital.

### 2.6 Otorisasi Supervisor Kasir (Security PIN Shield)
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

---

## 4. Keterkaitan Lintas Modul

* **Ke Modul Inventory:** Mengurangi saldo stok secara atomik (*atomic stock decrement*) untuk mencegah *race condition* saat kasir ramai.
* **Ke Modul Finance:** Memperbarui saldo kas laci kasir dan memicu `AutoJournalService`.
* **Ke Modul CRM:** Menambahkan poin loyalitas pelanggan dan mencatat riwayat pembelian pelanggan.
* **Ke Modul WhatsApp:** Mengirimkan pesan WhatsApp otomatis berisi ringkasan nota dan tautan struk web resmi.
