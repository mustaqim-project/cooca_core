# Aturan Bisnis Gudang & Inventori (Inventory Business Rules)

> **Status:** COMPLETE  
> **Mandat:** Menjamin akurasi saldo fisik barang di gudang/toko, mencegah selisih stok, dan melindungi konsistensi resep produk.

---

## 📜 Katalog Aturan Bisnis Inventori

### RULE-INV-001: Negative Stock Guard & Tenant Policy
* **Name:** Kebijakan Pengendalian Stok Minus
* **Deskripsi:** Sistem mendukung flag konfigurasi per bisnis `allow_negative_stock`:
  - Jika `false` (default ketat): Sistem menolak mutasi keluar di kasir POS atau gudang jika kuantitas yang diminta melebihi sisa stok fisik yang tersedia.
  - Jika `true` (mode toleran operasional): Sistem mengizinkan transaksi kasir berlanjut dengan mencatat saldo stok bernilai negatif, namun menampilkan penanda peringatan stok kritis agar pemilik toko segera melakukan *Goods Receipt* atau *Stock Opname*.
* **Alasan:** Fleksibilitas bagi pedagang ritel cepat yang terkadang fisik barangnya sudah ada di toko namun faktur penerimaan belum sempat diinput staf kasir.

### RULE-INV-002: Atomic Stock Deduction & Concurrency Lock
* **Name:** Pemotongan Stok Atomik & Anti-Race Condition
* **Deskripsi:** Seluruh pengurangan stok produk langsung maupun bahan baku resep BOM wajib dieksekusi secara atomik menggunakan database pessimistic lock (`lockForUpdate()`) atau query decrement langsung:
  ```php
  InventoryStock::where('id', $stockId)->decrement('quantity', $qtyDeducted);
  ```
* **Alasan:** Mencegah *race condition* atau *overselling* saat dua kasir atau pesanan online memproses item yang sama pada detik yang bersamaan.

### RULE-INV-003: Active BOM Recipe Dependency Shield
* **Name:** Perlindungan Penghapusan Bahan Baku Aktif
* **Deskripsi:** Bahan baku mentah (*Material*) yang tercatat aktif dalam resep produk (*BOM Item*) atau masih memiliki catatan transaksi stok yang belum tuntas **DILARANG DIHAPUS** secara permanen.
* **Perilaku:** Sistem menolak aksi delete dan memberikan opsi penonaktifan (*Archive / Inactive Status*) agar tidak mengacaukan kalkulasi HPP produk terkait.

### RULE-INV-004: Material Unit Conversion Precision
* **Name:** Presisi Konversi Multi-Satuan Bahan Baku
* **Deskripsi:** Faktor konversi antara satuan beli dan satuan pakai resep wajib menggunakan rasio desimal presisi tinggi (minimal 4 digit desimal) untuk meminimalkan akumulasi pembulatan selisih gram/mililiter dalam produksi massal.

### RULE-INV-005: Bundle Bottleneck Stock Rule
* **Name:** Aturan Stok Efektif Paket Kombo / Bundling (Bottleneck Rule)
* **Deskripsi:** Produk kombo (`is_bundle = true`) tidak memiliki stok fisik mandiri. Ketersediaan stok dihitung dinamis dari stok fisik item anak dibagi rasio kebutuhan:
  $$\text{Effective Bundle Stock} = \min_{i=1}^{n} \left( \left\lfloor \frac{\text{Physical Stock}(\text{Child}_i)}{\text{Required Qty}(\text{Child}_i)} \right\rfloor \right)$$
* **Perilaku:** Jika salah satu stok produk anak habis atau kurang dari rasio kebutuhan per paket, stok kombo otomatis bernilai `0.0`. Dilarang menampilkan stok tersedia jika salah satu komponen anak kosong.

### RULE-INV-006: Recursive Multi-Item Bundle Stock Deduction & Restoration
* **Name:** Pemotongan & Pemulihan Stok Atomik Rekursif Paket Kombo
* **Deskripsi:** Penjualan paket kombo di POS wajib memotong stok seluruh komponen anak secara atomik di dalam transaksi basis data:
  - Jika item anak adalah produk fisik langsung, kurangi stok pada tabel `inventory_stocks` dan catat mutasi `sale`.
  - Jika item anak memiliki resep BOM, rekursi `getMaterialDeductions()` (Case 0) untuk memotong bahan baku mentah dari dapur/gudang terkait.
  - Saat transaksi kombo di-refund/void, seluruh stok item anak dan bahan baku wajib dipulihkan kembali (*auto-restock*).

