# Modul Gudang & Inventori Multi-Lokasi (Inventory Management)

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Domain/Inventory/`, `app/Domain/Material/`, `app/Domain/Product/`, `app/Domain/Purchasing/`  
> **Tabel Basis Data:** `materials`, `material_categories`, `material_prices`, `material_unit_conversions`, `products`, `product_categories`, `inventory_stocks`, `inventory_movements`, `inventory_adjustments`, `inventory_stock_opnames`, `goods_receipts`, `goods_receipt_items`

---

## 1. Tujuan & Nilai Bisnis

Modul Inventori Cooca mengelola seluruh arus pergerakan barang secara akurat, mulai dari penerimaan bahan mentah dari pemasok, transfer antar gudang/cabang, penyesuaian fisik (*stock opname*), hingga pemotongan stok otomatis saat penjualan terjadi di kasir atau toko online.

Modul ini membedakan secara tegas antara **Bahan Baku Mentah (*Materials*)** dan **Produk Siap Jual (*Products*)**, didukung oleh konversi multi-satuan dinamis dan metode valuasi persediaan ilmiah (*Moving Average Costing*).

---

## 2. Pemisahan Entitas: Bahan Baku vs Produk Jadi

Cooca memisahkan data inventori menjadi dua entitas spesifik:

```
┌──────────────────────────────────────┐     ┌──────────────────────────────────────┐
│       BAHAN BAKU (MATERIALS)         │     │         PRODUK JADI (PRODUCTS)       │
├──────────────────────────────────────┤     ├──────────────────────────────────────┤
│ • Komponen mentah / bumbu / kain /   │     │ • Barang yang dijual ke pelanggan    │
│   kemasan botol / dus.               │     │ • Tipe: Barang Fisik / Jasa Servis   │
│ • Memiliki satuan beli & satuan pakai│     │ • Memiliki harga jual kasir & barcode│
│ • Digunakan di Resep BOM produk      │     │ • Mengurangi stok langsung ATAU      │
│ • Dibeli melalui PO / Vendor Bill    │     │   mengurangi bahan mentah via BOM    │
└──────────────────────────────────────┘     └──────────────────────────────────────┘
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
  - `type`: *in*, *out*, *adjustment*, *transfer*, *waste*.
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

---

## 4. Aturan Bisnis Inventori (Business Rules)

* **RULE-INV-001 (Negative Stock Guard):** Jika flag bisnis `allow_negative_stock = false`, sistem memblokir mutasi keluar yang menyebabkan saldo $< 0$.
* **RULE-INV-002 (Material Deletion Shield):** Bahan baku yang sedang digunakan dalam resep BOM aktif atau memiliki saldo persediaan tidak boleh dihapus dari sistem (*protected foreign key*).
* **RULE-INV-003 (Immutable Historical Movements):** Catatan mutasi stok lama yang telah terekam bersifat permanen dan tidak dapat diedit atau dihapus secara manual.

---

## 5. Keterkaitan Lintas Modul

* **Ke Modul Purchasing:** Menerima barang fisik dari Surat Pesanan (PO) dan memperbarui saldo stok.
* **Ke Modul POS & Sales:** Mengurangi stok saat transaksi berhasil dibayar.
* **Ke Modul Finance & Accounting:** Nilai persediaan dihitung otomatis dan tercermin pada Akun Persediaan di Laporan Neraca (*Balance Sheet*).
