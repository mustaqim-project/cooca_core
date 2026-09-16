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
