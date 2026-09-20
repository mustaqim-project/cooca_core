# PRD-03: Evolusi Keuangan Korporasi (Multi-Ledger) Tanpa Kehilangan Data

**ID Dokumen:** `PRD-03-FINANCE-MULTI-LEDGER`  
**Modul:** Keuangan, Akuntansi & General Ledger  
**Penanggung Jawab:** Principal Backend & Accounting Domain Architect  
**Status:** READY FOR IMPLEMENTATION  
**Target Pengguna:** Pemilik Usaha, Akuntan Perusahaan, Manajer Keuangan, Auditor  

---

## 1. Audit Sistem Existing (As-Is State)

### A. File & Komponen Terkait
* **Mesin Jurnal Otomatis:** [`app/Domain/Accounting/AutoJournalService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Accounting/AutoJournalService.php) (36 KB, 860 baris)
* **Model Akuntansi:**
  - [`app/Models/Account.php`](file:///c:/laragon/www/cooca_core/app/Models/Account.php) (Chart of Accounts / Bagan Akun Standar)
  - [`app/Models/JournalEntry.php`](file:///c:/laragon/www/cooca_core/app/Models/JournalEntry.php) (Header Jurnal Umum)
  - [`app/Models/JournalEntryLine.php`](file:///c:/laragon/www/cooca_core/app/Models/JournalEntryLine.php) (Baris Debit / Kredit)
  - [`app/Models/CashAccount.php`](file:///c:/laragon/www/cooca_core/app/Models/CashAccount.php) (Kas & Rekening Bank)
* **Controller:** [`app/Http/Controllers/Web/Finance/FinanceWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Finance/FinanceWebController.php)

### B. Temuan & Fakta Arsitektur Existing
1. **Penemuan Luar Biasa di Backend:**  
   Backend COOCA ternyata **sudah memiliki mesin pembukuan berpasangan (*double-entry bookkeeping*) penuh** di dalam [`AutoJournalService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Accounting/AutoJournalService.php).
   - Setiap transaksi POS kasir $\rightarrow$ otomatis membuat jurnal Debit Kas/Piutang dan Kredit Penjualan & HPP.
   - Setiap pembelian PO / Tagihan Supplier $\rightarrow$ otomatis menjurnal Persediaan dan Hutang Usaha.
   - Setiap biaya operasional $\rightarrow$ otomatis menjurnal Beban dan Kas/Bank.
2. **Kesenjangan / Gap di Antarmuka Pengguna (UI):**
   - Di level UMKM, menu yang ditampilkan sangat disederhanakan: hanya "Kas & Bank", "Catat Pengeluaran", "Hutang", dan "Piutang". Istilah akuntansi sengaja disembunyikan agar pemilik toko tidak pusing.
   - Namun untuk korporasi, **UI akuntansi tingkat lanjut belum dibuka**:
     - Belum ada antarmuka pohon bagan akun kustom (*Custom Chart of Accounts Tree: Induk & Sub-Akun*).
     - Belum ada tampilan Laporan Neraca Keuangan resmi (*Balance Sheet: Aset = Kewajiban + Ekuitas*).
     - Belum ada Laporan Neraca Saldo (*Trial Balance*).
     - Belum ada modul Rekonsiliasi Bank (pencocokan rekening koran bank vs catatan kasir).

---

## 2. Strategi "Zero Data Loss" (Migrasi Tanpa Kehilangan Data)

Karena [`AutoJournalService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Accounting/AutoJournalService.php) sudah membukukan setiap transaksi ke dalam tabel `journal_entries` dan `journal_entry_lines` sejak hari pertama UMKM berdiri:

> **Jaminan Arsitektur:** Saat pemilik bisnis beralih dari segmen **UMKM** ke **KORPORASI**, sistem **TIDAK PERLU melakukan migrasi data manual atau reset database**.  
> Seluruh rekam jejak jurnal historis sudah tersedia utuh di database. Sistem cukup **mengaktifkan antarmuka laporan akuntansi tingkat lanjut**, dan seluruh laporan Neraca serta Buku Besar langsung tersaji seketika dari data yang sudah terkumpul!

---

## 3. Fitur yang Ditambahkan untuk Segmen Korporasi

### A. Pengelola Bagan Akun Kustom (Custom COA Tree Builder)
* Mengizinkan akuntan perusahaan menambah sub-akun hierarkis tak terbatas:
  - `1000 - ASET LANCAR`
    - `1100 - Kas & Setara Kas`
      - `1101 - Kas Kasir Gerai Sudirman`
      - `1102 - Rekening BCA Operasional`
      - `1103 - Rekening Mandiri Payroll`
* Mengunci akun sistem utama (*locked system accounts*) agar integritas jurnal otomatis tetap terlindungi.

### B. Laporan Neraca Keuangan Standar SAK EMKM / PSAK (Balance Sheet)
* Menghitung posisi keuangan perusahaan pada tanggal tertentu:
  - **Total Aset** (Aset Lancar + Aset Tetap - Akumulasi Penyusutan).
  - **Total Kewajiban** (Hutang Usaha + Hutang Gaji + Hutang Pajak).
  - **Total Ekuitas** (Modal Pemilik + Laba Ditahan + Laba Bersih Periode Berjalan).
* Rumus Keseimbangan Wajib Valid: $\text{Aset} = \text{Kewajiban} + \text{Ekuitas}$.

### C. Laporan Neraca Saldo (Trial Balance)
* Menampilkan daftar seluruh akun beserta total saldo Debit dan Kredit.
* Dilengkapi indikator keseimbangan Apple HIG: *"Neraca Saldo Seimbang (Debit = Kredit = Rp X.XXX.XXX)"*.

### D. Buku Besar Umum (General Ledger Drilldown)
* Akuntan dapat mengklik akun mana pun untuk melihat seluruh histori mutasi debit-kredit beserta tautan dokumen sumber (Nomor Invoice, Nomor PO, atau Nomor Struk Kasir).

### E. Modul Rekonsiliasi Bank Sederhana
* Memungkinkan staf keuangan mengunggah mutasi rekening koran bank (format CSV / Excel).
* Sistem otomatis mencocokkan tanggal dan nominal transaksi mutasi dengan data penerimaan kasir COOCA (*Auto-Match Matcher*).

---

## 4. Alur Kerja Akuntansi Korporasi

```
[ TRANSAKSI DI SEMUA SALURAN ]
(Kasir POS, B2B Invoice, Tagihan Supplier, Payroll Gaji)
               │
               ▼
[ AutoJournalService.php ]
(Membuat JournalEntry & JournalEntryLine secara otomatis)
               │
               ├───────────────────────────────────────────┐
               ▼                                           ▼
      [ JIKA MODE UMKM ]                         [ JIKA MODE KORPORASI ]
  • Tampilan Kas & Bank Sederhana           • Tampilan Akuntansi Lengkap
  • Catat Pengeluaran Cepat                 • Pohon Bagan Akun (COA Tree)
  • Laporan Laba Rugi 1-Halaman             • Laporan Neraca Keuangan (Balance Sheet)
                                            • Laporan Neraca Saldo (Trial Balance)
                                            • Buku Besar & Jurnal Penyesuaian
                                            • Rekonsiliasi Bank
```

---

## 5. Kriteria Keberhasilan (Definition of Done)
1. Beralih dari segmen UMKM ke Korporasi tidak merusak atau menghilangkan transaksi historis apa pun.
2. Laporan Neraca Keuangan menghasilkan nilai aktiva dan pasiva yang seimbang (balance) 100%.
3. Akuntan korporasi dapat membuat sub-akun kustom pada bagan akun (COA).
4. Setiap baris jurnal terhubung dengan tautan dokumen sumber transaksi aslinya.
