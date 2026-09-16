# Aturan Bisnis Keuangan & Finansial (Financial Business Rules)

> **Status:** COMPLETE  
> **Mandat:** Mandatory & Non-Destructive. Aturan-aturan ini menjaga integritas buku kas, rekonsiliasi, dan akuntansi berimbang di Cooca.

---

## 📜 Katalog Aturan Bisnis Finansial

### RULE-FIN-001: Immutability of Finalized Financial Records
* **Name:** Ketetapan Catatan Transaksi Selesai (Anti-Tampering)
* **Deskripsi:** Nilai transaksi pada nota penjualan kasir (POS), invoice faktur komersial, pesanan toko online, dan bukti penerimaan kas yang berstatus `paid` atau `completed` **DILARANG KERAS** dimodifikasi langsung di database atau formulir edit.
* **Alasan:** Perubahan nilai transaksi selesai akan merusak jejak audit perpajakan, merusak saldo buku kas historis, dan menimbulkan ketidakseimbangan akuntansi.
* **Prosedur Koreksi:** Jika terjadi salah input kasir atau pembatalan transaksi, koreksi WAJIB dilakukan melalui penerbitan dokumen penyeimbang:
  - *Sales Return (Retur Penjualan)* untuk pengembalian barang.
  - *Refund / Credit Note* untuk pengembalian dana kas.
  - *Adjustment Journal* untuk koreksi pembukuan.

### RULE-FIN-002: Strict Balanced Double-Entry Journaling
* **Name:** Kewajiban Keseimbangan Jurnal Ganda ($\sum \text{Debit} = \sum \text{Kredit}$)
* **Deskripsi:** Setiap entri jurnal yang dihasilkan oleh `AutoJournalService` atau jurnal memorial manual wajib memiliki total nilai Debit yang setara persis dengan total nilai Kredit.
* **Trigger:** Penjualan POS, Faktur Penjualan, Penerimaan Barang (GR), Pelunasan Tagihan, Pengeluaran Biaya, dan Penutupan Kasir.
* **Perilaku:** Jika terdeteksi selisih $\Delta \neq 0$, transaksi database di-*rollback* seketika dan mencatat log kritis untuk audit developer, mencegah timbulnya neraca tidak seimbang.

### RULE-FIN-003: Financial Multi-Tenant Context Scoping
* **Name:** Isolasi Ketat Akses Rekening & Transaksi Keuangan
* **Deskripsi:** Setiap query Eloquent dan Query Builder yang mengakses saldo akun kas, transaksi kasir, hutang-piutang, atau laporan laba rugi **WAJIB** menyertakan scope bisnis aktif:
  ```php
  $business = \App\Support\Context::requireBusiness();
  $accounts = CashAccount::where('business_id', $business->id)->get();
  ```
* **Alasan:** Mencegah kebocoran data finansial sensitif antar penyewa (tenant) platform Cooca.

### RULE-FIN-004: Standard Moving Average COGS Formula
* **Name:** Standardisasi Rumus Harga Pokok Penjualan (HPP)
* **Deskripsi:** Nilai modal persediaan dihitung menggunakan metode Moving Average Cost berbobot. Dilarang mengubah formula dasar matematika HPP:
  $$\text{HPP Baru} = \frac{(\text{Stok Lama} \times \text{HPP Lama}) + (\text{Qty Masuk} \times \text{Harga Masuk})}{\text{Stok Lama} + \text{Qty Masuk}}$$
* **Alasan:** Menjamin konsistensi valuasi persediaan dan perhitungan margin laba kotor bisnis.
