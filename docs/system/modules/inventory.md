# Modul Gudang & Inventori Multi-Lokasi (Inventory Management)

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Domain/Inventory/`, `app/Domain/Material/`, `app/Domain/Product/`, `app/Domain/Purchasing/`  
> **Tabel Basis Data:** `materials`, `material_categories`, `material_prices`, `material_unit_conversions`, `products`, `product_categories`, `product_bundle_items`, `inventory_stocks`, `inventory_movements`, `inventory_adjustments`, `inventory_stock_opnames`, `goods_receipts`, `goods_receipt_items`

---

## 1. Tujuan & Nilai Bisnis

Modul Inventori Cooca mengelola seluruh arus pergerakan barang secara akurat, mulai dari penerimaan bahan mentah dari pemasok, transfer antar gudang/cabang, penyesuaian fisik (*stock opname*), hingga pemotongan stok otomatis saat penjualan terjadi di kasir atau toko online.

Modul ini membedakan secara tegas antara **Bahan Baku Mentah (*Materials*)**, **Produk Siap Jual Tunggal (*Products*)**, dan **Paket Kombo / Bundling (*Bundle Products*)**, didukung oleh konversi multi-satuan dinamis dan metode valuasi persediaan ilmiah (*Moving Average Costing*).

---

## 2. Pemisahan Entitas: Bahan Baku vs Produk Jadi vs Paket Kombo

Cooca memisahkan data inventori menjadi tiga entitas spesifik:

```
┌───────────────────────────┐  ┌───────────────────────────┐  ┌───────────────────────────┐
│  BAHAN BAKU (MATERIALS)   │  │  PRODUK JADI (PRODUCTS)   │  │   PAKET KOMBO (BUNDLES)   │
├───────────────────────────┤  ├───────────────────────────┤  ├───────────────────────────┤
│ • Komponen mentah / bumbu │  │ • Barang fisik / jasa jual│  │ • Paket hemat multi-produk│
│ • Satuan beli & pakai     │  │ • Memiliki stok fisik     │  │ • is_bundle = true        │
│ • Komponen resep BOM      │  │ • Potong stok atau via BOM│  │ • Stok: Bottleneck Rule   │
│ • Dibeli via PO / Vendor  │  │ • Harga jual & barcode SKU│  │ • Potong rekursif anak/BOM│
└───────────────────────────┘  └───────────────────────────┘  └───────────────────────────┘
```

---

## 3. Fitur Utama Inventori

### 3.1 Konversi Multi-Satuan Dinamis (Unit Conversions)
* **Satuan Beli (*Purchase Unit*):** Satuan saat membeli dari supplier (misal: *1 Karung*, *1 Box isi 24*, *1 Jerigen 5 Liter*).
* **Satuan Pakai/Resep (*Base / Recipe Unit*):** Satuan terkecil yang digunakan pada resep atau produksi (misal: *gram*, *ml*, *pcs*).
* Sistem secara otomatis menghitung konversi rasio saat penerimaan barang sehingga stok gudang selalu tersimpan dalam satuan dasar yang presisi.

### 3.2 Penerimaan Barang Fisik (Goods Receipt / GR)
* **Verifikasi Surat Jalan Vendor:** Staf gudang mencocokkan jumlah fisik yang datang dengan Purchase Order (PO) yang telah diterbitkan.
* **Partial Receipt Support:** Mendukung penerimaan bertahap jika supplier mengirim pesanan dalam beberapa kali pengiriman.
* **Auto-Cost Updating (Moving Average):** Saat barang masuk dengan harga baru, sistem secara otomatis menghitung ulang harga modal rata-rata:
  $$\text{HPP Rata-Rata Baru} = \frac{(\text{Stok Lama} \times \text{HPP Lama}) + (\text{Qty Masuk} \times \text{Harga Beli Baru})}{\text{Total Stok Baru}}$$

### 3.3 Kartu Stok & Jejak Mutasi (Stock Movement Audit Trail)
* Setiap perubahan stok mencatat record mutasi di tabel `inventory_movements` dengan atribut:
  - `type`: *in*, *out*, *adjustment*, *transfer*, *waste*, *sale*.
  - `quantity`: Jumlah mutasi positif/negatif.
  - `balance_after`: Saldo akhir fisik tepat setelah mutasi.
  - `reference_type` & `reference_id`: Sumber dokumen (PO, Invoice, POS Order, Resep BOM).

### 3.4 Stock Opname & Penyesuaian Fisik (Stock Opname & Blind Count)
* Memfasilitasi penghitungan fisik berkala tanpa menghentikan operasional toko.
* Sistem mendeteksi selisih fisik (*variance*), menghitung nilai rupiah kerugian (*loss valuation*), dan otomatis menerbitkan jurnal penyesuaian biaya persediaan ke buku besar akuntansi.

### 3.5 Pemotongan Stok Resep Otomatis (Auto-BOM Deduction)
* Saat kasir menjual 1 porsi "Kopi Susu Gula Aren", sistem secara instan memotong:
  - Biji Kopi: $18\text{ gram}$
  - Susu UHT: $120\text{ ml}$
  - Sirup Gula Aren: $25\text{ ml}$
  - Cup Plastik & Sedotan: $1\text{ pcs}$
* Mencegah selisih bahan baku tak terlacak di dapur/gudang.

### 3.6 Mesin Paket Kombo & Pemotongan Rekursif (Combo & Bundling Engine)
* **Konfigurasi Master Produk:** Pengguna dapat mengaktifkan opsi kombo (`is_bundle = true`) dan memilih produk-produk anak via dynamic repeater pada modal produk.
* **Aturan Stok Bottleneck:** Produk kombo tidak memiliki saldo fisik mandiri. Stok efektif dihitung berdasarkan stok terkecil item anak yang menyusunnya:
  $$\text{Stok Kombo} = \min_{i=1}^{n} \left( \left\lfloor \frac{\text{Stok Fisik}(\text{Child}_i)}{\text{Kebutuhan}(\text{Child}_i)} \right\rfloor \right)$$
* **Pemotongan Stok Atomik Rekursif (`StockService::deductForProductSale`):** Saat kasir menjual paket kombo, sistem secara otomatis:
  1. Mengurangi saldo stok fisik untuk setiap produk anak bertipe barang jadi.
  2. Merekursi resep BOM untuk produk anak bertipe olahan/resep dan memotong bahan baku mentah terkait.
* **Pemulihan Stok Refund Rekursif (`StockService::restoreForPosRefund`):** Jika terjadi refund atau void transaksi kasir, stok seluruh produk anak dan bahan baku dikembalikan secara presisi ke saldo inventori.

### 3.7 Galeri Foto Multi-Image & Thumbnail Produk (`product_images`)
* **Pemisahan Peran Media:**
  - **Thumbnail Utama (`products.image_path`):** Satu gambar utama yang dioptimalkan untuk performa tinggi pada Kasir POS, Cetak Struk/Faktur, dan Sinkronisasi Marketplace Hub.
  - **Galeri Foto Tambahan (`product_images`):** Tabel terpisah yang dapat menampung hingga 10 foto per produk dengan atribut `image_path`, `caption`, `sort_order`, dan `is_primary`.
* **Multi-Tenant Storage & Quota Tracking:** Setiap unggahan berkas thumbnail dan galeri divalidasi batas kuota bisnis melalui `StorageTrackingService::assertCanUpload()`, diunggah ke storage terisolasi `TenantStorage::publicDir($business, 'products')`, dan dicatat ke `storage_files`.
* **Cascade Deletion:** Saat master produk dihapus, seluruh berkas galeri dan entitas `product_images` otomatis dibersihkan dari penyimpanan tenant.

---

## 4. Aturan Bisnis Inventori (Business Rules)

* **RULE-INV-001 (Negative Stock Guard):** Jika flag bisnis `allow_negative_stock = false`, sistem memblokir mutasi keluar yang menyebabkan saldo $< 0$.
* **RULE-INV-002 (Material Deletion Shield):** Bahan baku yang sedang digunakan dalam resep BOM aktif atau memiliki saldo persediaan tidak boleh dihapus dari sistem (*protected foreign key*).
* **RULE-INV-003 (Immutable Historical Movements):** Catatan mutasi stok lama yang telah terekam bersifat permanen dan tidak dapat diedit atau dihapus secara manual.
* **RULE-INV-004 (Material Unit Conversion Precision):** Faktor konversi antara satuan beli dan satuan pakai resep wajib menggunakan rasio desimal presisi tinggi (minimal 4 digit desimal).
* **RULE-INV-005 (Bundle Bottleneck Stock Rule):** Stok produk kombo wajib dihitung secara dinamis dari produk anak. Jika salah satu stok anak tidak mencukupi rasio kuantitas minimalnya, maka stok kombo otomatis bernilai `0.0`.
* **RULE-INV-006 (Recursive Multi-Item Bundle Stock Deduction & Restoration):** Penjualan paket kombo dilarang memotong stok semu pada entitas parent; pemotongan dan pengembalian wajib direkursi secara atomik ke seluruh komponen anak (fisik maupun BOM resep anak).

---

## 5. Keterkaitan Lintas Modul

* **Ke Modul Purchasing:** Menerima barang fisik dari Surat Pesanan (PO) dan memperbarui saldo stok.
* **Ke Modul POS & Sales:** Mengurangi stok saat transaksi berhasil dibayar (langsung, via BOM, atau rekursif kombo).
* **Ke Modul Finance & Accounting:** Nilai persediaan dihitung otomatis dan tercermin pada Akun Persediaan di Laporan Neraca (*Balance Sheet*).
* **Dokumentasi Alur Terkait:**
  - [`docs/system/workflows/purchasing-goods-receipt-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/purchasing-goods-receipt-flow.md) - Alur Pengadaan PO & Penerimaan Barang (GR).
  - [`docs/system/workflows/pos-sales-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/pos-sales-flow.md) - Alur Pemotongan Stok Kasir POS.
  - [`docs/system/workflows/product-bundling-and-combo-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/product-bundling-and-combo-flow.md) - Alur Kerja Lengkap Paket Kombo & Bundling.

---

## 6. Standar Antarmuka (Bento Apple HIG v2.0) & Ergonomi Mobile/Tablet

* **Bento Grid & Segmented Controls:** Seluruh antarmuka manajemen cabang, gudang logistik (`warehouse.index`, `warehouse.show`), katalog produk (`products.index`), resep BOM (`products.bom`), dan varian modifiers (`pos.modifiers.index`) menggunakan Bento Apple HIG v2.0 dengan toolbar macOS Sonoma (`backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80`).
* **Bento Box Kombo & Multi-Harga Saluran:** Modal tambah/ubah produk (`products/index.blade.php`) dilengkapi bento box konfigurasi kombo (switch aktivasi, child items dynamic repeater, live HPP/normal value estimation) serta bento box penetapan harga kanal penjualan POS F&B.
* **Zero-Emoji & Semantic Lucide Icons:** Seluruh breadcrumb navigasi dan tombol aksi menggunakan ikon Lucide SVG murni (`chevron-right`, `warehouse`, `package`, `plus`, dll.) tanpa simbol unicode/emoji mentah.
* **Touch-Friendly & Anti Auto-Zoom:** Touch targets tombol modal disetel $\ge 44\text{px}$ (`min-h-[44px]` / `h-11`), form input mobile menggunakan font-size $\ge 16\text{px}$ (`text-[16px] sm:text-[14px]`) untuk mencegah auto-zoom Safari, dan navigasi bawah dilengkapi safe-padding `pb-28 lg:pb-12`.
* **Proteksi Anti-Fraud Multi-Tenant:** Isolasi data tenant dijamin melalui `Context::requireBusiness()`, penyesuaian stok wajib mencantumkan alasan mutasi (*reason required*) dan validasi unit cost untuk integritas audit trail.
