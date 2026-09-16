# Modul Keuangan & Akuntansi (Finance & Double-Entry Accounting)

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Domain/Finance/`, `app/Domain/Accounting/`  
> **Tabel Basis Data:** `cash_accounts`, `cash_transactions`, `payment_accounts`, `payment_settlement_allocations`, `supplier_invoices`, `supplier_payments`, `invoices`, `journal_entries`, `journal_lines`, `chart_of_accounts`

---

## 1. Tujuan & Nilai Bisnis

Modul Keuangan Cooca merajut seluruh denyut transaksi bisnis-mulai dari penerimaan uang tunai/digital di kasir POS, pelunasan faktur vendor, penagihan piutang pelanggan B2B, hingga beban biaya operasional harian-menjadi satu kesatuan pembukuan yang **otomatis, akurat, dan berimbang**.

Pemilik bisnis tidak perlu lagi menyewa akuntan khusus hanya untuk menjurnal transaksi harian atau membuat buku besar manual; Cooca menjalankan **Otomatisasi Pembukuan & Jurnal Ganda (*Auto-Journaling Engine*)** secara otonom di latar belakang.

---

## 2. Tiga Pilar Utama Keuangan Cooca

1. **Integrasi Real-Time (Zero-Manual Recap):**  
   Setiap transaksi di kasir POS, pembayaran PO gudang, atau invoice penjualan langsung memperbarui saldo kas/bank dan kartu buku besar detik itu juga.
2. **Pembukuan Berpasangan (*Double-Entry Bookkeeping*):**  
   Setiap transaksi otomatis menghasilkan jurnal berpasangan Debit dan Kredit yang identik, menjamin persamaan dasar akuntansi:
   $$\text{Aset} = \text{Kewajiban} + \text{Ekuitas}$$
3. **Audit Trail & Idempotency:**  
   Setiap aliran dana memiliki referensi dokumen asli (`reference_type`, `reference_id`), saldo berjalan (*balance after*), serta ID pencatat untuk mencegah manipulasi maupun duplikasi mutasi.

---

## 3. Fitur Utama Modul Keuangan

### 3.1 Pusat Kas & Rekening Bank (Cash & Bank Accounts)
* Mendukung multi-akun likuid:
  - *Kas Operasional Toko / Kasir Utama*
  - *Kas Kecil (Petty Cash)*
  - *Rekening Bank Transfer (BCA, Mandiri, BRI, BNI)*
  - *Akun Settlement QRIS / EDC*
* **Mutasi Cepat Kas Masuk / Keluar:** Tombol 1-klik untuk mencatat pendapatan lain-lain atau pengeluaran operasional mendesak tanpa navigasi berbelit.
* **Transfer Antar Rekening:** Pemindahan dana dari laci kasir ke rekening bank operasional dengan jurnal penyeimbang otomatis.

### 3.2 Pelacakan Multi-Kanal Kasir POS (Multi-Channel Cashier Settlement)
* Memisahkan penerimaan kasir berdasarkan kanal bayar secara terperinci:
  - **Uang Tunai Fisik:** Masuk ke saldo kas laci register kasir.
  - **QRIS & EDC Digital:** Masuk ke akun penampungan settlement sementara sebelum ditarik ke bank.
  - **Kasbon Piutang (AR):** Otomatis mendebit akun Piutang Usaha atas nama pelanggan terkait.

### 3.3 Hutang Usaha Pemasok (Accounts Payable / AP)
* **Tagihan Vendor (*Supplier Invoices*):** Terbit otomatis atau manual berdasarkan penerimaan barang (*Goods Receipt*).
* **Jadwal Jatuh Tempo & Pengingat:** Sistem memantau tanggal jatuh tempo faktur vendor dan memberikan sinyal peringatan ramah pengguna sebelum denda keterlambatan terjadi.
* **Pembayaran Bertahap (*Partial Payment*):** Mendukung pelunasan bertahap dengan pencatatan sisa saldo hutang yang transparan.

### 3.4 Piutang Usaha Pelanggan (Accounts Receivable / AR)
* Mencatat faktur penjualan tempo pelanggan B2B dan bon kasir.
* **Aging Schedule (Analisis Umur Piutang):** Mengklasifikasikan piutang pelanggan ke dalam kelompok *Lancar (Current)*, *1-30 Hari*, *31-60 Hari*, dan *>60 Hari*.
* **Pengingat Santun via WhatsApp:** Tombol 1-klik untuk mengirim pesan pengingat tagihan santun dan profesional ke WhatsApp pelanggan lengkap dengan link invoice digital.

### 3.5 Buku Kas & Ledger Berjalan (Real-Time Cash Ledger)
* Menampilkan mutasi transaksi kronologis dengan saldo berjalan (*running balance*).
* Memungkinkan audit cepat jika terjadi perbedaan fisik uang di laci kasir atau rekening koran bank.

### 3.6 Otomasi Jurnal Ganda (AutoJournalService)
* Mesin cerdas yang memetakan aktivitas operasional ke akun COA (*Chart of Accounts*) secara otomatis:

| Peristiwa Bisnis | Akun Debit | Akun Kredit |
| :--- | :--- | :--- |
| **Penjualan Kasir (Tunai)** | Kas Kasir (Aset) | Pendapatan Penjualan (Pendapatan) |
| **Penjualan Kasir (Kasbon)** | Piutang Usaha (Aset) | Pendapatan Penjualan (Pendapatan) |
| **Penerimaan Barang (GR)** | Persediaan Bahan (Aset) | Hutang Usaha / AP (Kewajiban) |
| **Pelunasan Hutang Vendor** | Hutang Usaha / AP (Kewajiban) | Rekening Bank / Kas (Aset) |
| **Pengeluaran Operasional** | Beban Operasional (Beban) | Kas Kecil / Bank (Aset) |
| **Beban HPP (Saat Jual)** | Beban Pokok Penjualan / HPP | Persediaan Barang (Aset) |

---

## 4. Laporan Keuangan Standar Eksekutif

1. **Laporan Laba Rugi (*Income Statement*):**  
   $$\text{Laba Bersih} = \text{Pendapatan} - \text{HPP (Beban Pokok)} - \text{Beban Operasional}$$
2. **Laporan Neraca (*Balance Sheet*):**  
   Menyajikan posisi Aset Lancar/Tetap, Kewajiban Jangka Pendek/Panjang, dan Ekuitas Modal Pemilik pada tanggal tertentu.
3. **Laporan Arus Kas (*Cash Flow Statement*):**  
   Arus kas dari Aktivitas Operasi, Investasi, dan Pendanaan.

---

## 5. Aturan Bisnis Finansial (Hard Financial Guardrails)

* **RULE-FIN-001 (Immutable Finalized Records):** Transaksi berstatus `paid` atau `completed` tidak boleh dimodifikasi nilainya demi menjaga integritas data historis.
* **RULE-FIN-002 (Balanced Journal Constraint):** Setiap entri jurnal umum WAJIB memiliki total debit yang sama persis dengan total kredit:
  $$\sum \text{Debit} - \sum \text{Kredit} = 0$$
* **RULE-FIN-003 (Strict Tenant Financial Scoping):** Seluruh query saldo kas, mutasi, dan laporan wajib discoped ke `Context::requireBusiness()` aktif.
