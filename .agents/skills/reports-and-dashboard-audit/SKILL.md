---
name: reports-and-dashboard-audit
description: Audit, perancangan, dan standardisasi sistem laporan bisnis (Laporan Keuangan, Penjualan Omnichannel [POS, Marketplace, Toko Online, Sales Order B2B], Inventori & Valuasi Stok, HRM & Penggajian), arsitektur dashboard Bento Apple HIG per domain, serta integrasi mesin ekspor profesional ke Excel (XLSX) dan PDF. Aktifkan saat user meminta audit laporan, pembuatan dashboard analitik, ekspor excel, laporan laba rugi, laporan stok, laporan omzet, laporan penjualan multisaluran, atau laporan HR.
---

# REPORTS, ANALYTICS & MULTI-DOMAIN DASHBOARD AUDIT SKILL (WITH EXCEL EXPORT ENGINE)

Skill operasional ini memandu AI Agent dalam mengeksekusi **audit, perancangan, dan standardisasi sistem pelaporan bisnis, dashboard analitik per domain, dan mesin ekspor data profesional ke Excel (XLSX) & PDF** di seluruh ekosistem COOCA ERP & POS v2.0.

Skill ini memastikan bahwa data operasional dari berbagai sumber (POS Kasir, Marketplace Hub, Toko Online, Gudang, Akuntansi, dan HRM) diagregasikan secara presisi matematis, disajikan dalam antarmuka **Bento Apple HIG v2.0**, dan dapat diekspor secara instan ke format **Microsoft Excel (XLSX)** dengan tata letak profesional siap cetak.

---

## 🧭 File Referensi Pendukung

* [`references/reports-matrix-and-export-standards.md`](file:///c:/laragon/www/cooca_core/.agents/skills/reports-and-dashboard-audit/references/reports-matrix-and-export-standards.md) — Matriks spesifikasi laporan bisnis, kalkulasi matematis, struktur kolom, dan standar styling file Excel (XLSX).
* [`references/domain-dashboards-blueprint.md`](file:///c:/laragon/www/cooca_core/.agents/skills/reports-and-dashboard-audit/references/domain-dashboards-blueprint.md) — Cetak biru tata letak Bento Grid Dashboard per domain (Keuangan, Penjualan Multisaluran, Inventori, HRM).
* [`c:/laragon/www/cooca_core/.agents/skills/cooca-agent-directive/references/design-system.md`](file:///c:/laragon/www/cooca_core/.agents/skills/cooca-agent-directive/references/design-system.md) — Master Design System Bento Apple HIG v2.0.

---

## 🏛️ 4 Pilar Utama Laporan & Dashboard Domain COOCA

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                   PETA 4 DOMAIN LAPORAN & DASHBOARD ANALITIK COOCA                     │
├─────────────────────────┬──────────────────────────┬───────────────────────────────────┤
│ 1. KEUANGAN & PEMBUKUAN │ 2. PENJUALAN MULTISALURAN│ 3. INVENTORI & GUDANG             │
│ • Laba Rugi (P&L)       │ • Kasir POS Offline      │ • Valuasi Stok (FIFO/Avg)         │
│ • Neraca (Balance Sheet)│ • Toko Online Storefront │ • Mutasi Masuk/Keluar (Movement)  │
│ • Arus Kas (Cash Flow)  │ • Marketplace Hub        │ • Sisa Stok Fisik vs Min Threshold│
│ • Buku Kas & Bank       │   (Shopee/Tokopedia/TT)  │ • Penerimaan Barang (PO / GR)     │
│ • Pajak UMKM (PPN, PB1) │ • Sales Order (B2B Sales)│ • Resep BOM & Scrap / Waste       │
├─────────────────────────┴──────────────────────────┴───────────────────────────────────┤
│ 4. SDM, PRESENSI & PENGGAJIAN (HRM & PAYROLL)                                          │
│ • Rekapitulasi Presensi GPS + Selfie • Keterlambatan & Lembur • Rekap Gaji & Slip Gaji │
│ • Kasbon & Komisi Sales/Kasir • Pajak PPh 21 TER • Rasio Biaya Gaji terhadap Omzet     │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 📊 1. Blueprint Laporan & Dashboard Keuangan (Financial Analytics)

### A. Laporan Keuangan Wajib:
1. **Laporan Laba Rugi (Income Statement / P&L):**
   - Pendapatan Bersih (Gross Sales - Diskon - Retur).
   - Harga Pokok Penjualan (HPP / COGS): Bahan Baku Terpakai + Upah Langsung + Beban Overhead Produksi.
   - Laba Kotor (Gross Profit) & Gross Profit Margin (%).
   - Beban Operasional (Gaji Staf, Sewa, Listrik/Air, Pemasaran, Penyusutan).
   - Laba Bersih Operasional (EBIT) & Laba Bersih Setelah Pajak (Net Profit).
2. **Laporan Neraca (Balance Sheet):**
   - Aset Lancar (Kas/Bank, Piutang Usaha, Persediaan Barang) + Aset Tetap = Total Aset.
   - Kewajiban Lancar (Hutang Usaha PO, Beban Akrual) + Ekuitas Modal = Total Pasiva.
3. **Laporan Arus Kas (Cash Flow Statement):**
   - Arus Kas dari Aktivitas Operasi, Investasi, dan Pendanaan.
4. **Buku Kas & Bank:** Mutasi penerimaan dan pengeluaran kasir harian, rekonsiliasi transfer bank.

### B. Widget KPI Dashboard Keuangan:
* **Kartu Bento Utama:** `Total Omzet Bersih`, `Laba Kotor (Gross Profit)`, `Laba Bersih (Net Profit)`, `Margin Laba (%)`.
* **Visualisasi Grafik:**
  - Tren Laba Rugi Bulanan (Bar chart: Pendapatan vs Beban vs Laba Bersih).
  - Struktur Beban Operasional (Donut chart: Gaji vs Sewa vs Bahan Baku vs Operasional).
  - Status Piutang & Hutang Jatuh Tempo (Aging Schedule: 1-30 hari, 31-60 hari, >60 hari).

---

## 🛍️ 2. Blueprint Laporan & Dashboard Penjualan Multisaluran (Omnichannel Sales)

### A. Sumber Saluran Transaksi (Channel Breakdown):
Sistem membedah dan mengelompokkan data penjualan dari 4 saluran mandiri:
1. **Kasir POS Offline (`pos`):** Transaksi kasir langsung, dine-in meja, takeaway, shift kasir, blind cash variance.
2. **Toko Online & Storefront (`storefront`):** Pesanan website publik merchant, kurir Biteship, order QR dine-in mandiri.
3. **Marketplace Hub (`marketplace`):** Transaksi terintegrasi Shopee, Tokopedia, TikTok Shop, Lazada.
4. **Sales Order B2B (`b2b_sales`):** Faktur kontrak grosir, pesanan PO perusahaan, pembayaran termin bertahap.

### B. Widget KPI Dashboard Penjualan:
* **Kartu Bento Utama:** `Total Penjualan Gabungan`, `Jumlah Transaksi`, `Rata-rata Nilai Transaksi (AOV)`, `Tingkat Retur / Void (%)`.
* **Visualisasi Grafik:**
  - Kontribusi Penjualan per Saluran (Multi-Bar / Donut: POS vs Marketplace vs Toko Online vs B2B SO).
  - Tren Penjualan Harian & Jam Ramai (*Peak Hours Heatmap*).
  - Top 10 Produk / Menu Terlaris (Volume Terjual & Kontribusi Nominal Omzet).
  - Performa Kasir / Sales Representative.

---

## 📦 3. Blueprint Laporan & Dashboard Inventori & Gudang (Inventory Health)

### A. Laporan Inventori Wajib:
1. **Laporan Valuasi Nilai Persediaan:** Total kuantitas $\times$ harga modal (Metode FIFO / Average Cost) per kategori dan gudang.
2. **Laporan Mutasi Stok (Stock Movement):** Stok Awal + Masuk (Pembelian/GR) - Keluar (POS/Sales/BOM) $\pm$ Penyesuaian = Stok Akhir.
3. **Laporan Stok Kritis & Menipis (Low Stock Alert):** Daftar item dengan sisa fisik $\le$ batas minimum buffer stock.
4. **Laporan Selisih Stok Opname:** Hasil pencocokan fisik vs sistem, selisih kuantitas, nilai selisih rupiah, dan riwayat approval supervisor.
5. **Laporan Konsumsi Bahan Resep BOM & Scrap:** Pemotongan bahan baku vs output barang jadi, efisiensi yield, dan toleransi waste.

### B. Widget KPI Dashboard Inventori:
* **Kartu Bento Utama:** `Total Nilai Aset Stok (Rp)`, `Jumlah SKU Aktif`, `Jumlah SKU Menipis / Kritis`, `Estimasi Hari Stok Bertahan (Days on Hand)`.
* **Visualisasi Grafik:**
  - Persebaran Nilai Stok Antar-Gudang / Cabang (Bar chart).
  - Klasifikasi Barang (*Fast Moving vs Slow Moving vs Dead Stock*).
  - Rasio Perputaran Persediaan (*Inventory Turnover Ratio - ITO*).

---

## 👥 4. Blueprint Laporan & Dashboard SDM & Penggajian (HRM & Payroll)

### A. Laporan HR Wajib:
1. **Laporan Rekapitulasi Presensi:** Total kehadiran kerja, keterlambatan (menit), pulang awal, izin, sakit, alpa, dan verifikasi radius GPS geofence + foto selfie.
2. **Laporan Rekapitulasi Gaji & Payroll:** Gaji Pokok + Tunjangan + Lembur + Komisi Penjualan - Potongan Kasbon - BPJS - PPh 21 TER = Gaji Bersih (Take-Home Pay).
3. **Laporan Kasbon Karyawan:** Plafon pinjaman, sisa saldo hutang kasbon, dan cicilan potongan gaji bulanan.
4. **Laporan Pajak PPh 21 Karyawan:** Perhitungan otomatis tarif efektif rata-rata (TER PPh 21) bulanan dan tahunan (Form 1721-A1).

### B. Widget KPI Dashboard HR:
* **Kartu Bento Utama:** `Tingkat Kehadiran Hari Ini (%)`, `Total Karyawan Aktif`, `Total Beban Gaji Bulan Ini`, `Rata-rata Keterlambatan (Menit)`.
* **Visualisasi Grafik:**
  - Rasio Beban Gaji terhadap Total Pendapatan (*Payroll-to-Revenue Ratio* - Ideal: 15–30%).
  - Produktivitas Karyawan (Omzet yang dihasilkan per staf kasir/sales).
  - Tren Kehadiran dan Lembur Mingguan.

---

## 📑 5. Standar Wajib Mesin Ekspor Microsoft Excel (XLSX Multi-Sheet) & PDF

Setiap proses ekspor data ke Excel **WAJIB MENERAPKAN STRUKTUR WORKBOOK DUA SHEET (OVERVIEW + DETAIL)** dan distyling dengan palet **Bento Apple HIG v2.0**:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│              STRUKTUR WORKBOOK EXCEL DUA SHEET & STYLING BENTO APPLE HIG               │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ SHEET 1: RINGKASAN EKSEKUTIF (EXECUTIVE OVERVIEW & KPI)                                │
│ • Header Formal: Nama Usaha (14pt Bold #1C1C1E), Judul Laporan (#007AFF), Periode, Jam │
│ • Bento KPI Box Grid: Total Nilai/Omzet, Total Volume, Rata-rata, Laba (Bg #F2F2F7,    │
│   Border #D1D1D6, Angka 13pt Bold #000000, NumberFormat Rp #,##0)                      │
│ • Tabel Agregasi Sub-Total: Breakdown per Saluran / Kategori (Header #1C1C1E Putih)    │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ SHEET 2: RINCIAN DATA LENGKAP (DETAILED TRANSACTIONAL & MOVEMENT LEDGER)               │
│ • Rincian data baris demi baris granular (Item-level detail, SKU, Jam, Kasir/Sales)   │
│ • Table Header Dark Onyx (#1C1C1E, Font Putih Bold, Row Height 26pt, Center Alignment) │
│ • AutoFilter Otomatis aktif pada seluruh kolom header (A4..LastCol4)                   │
│ • Freeze Panes pada baris data pertama ('A5') agar header terkunci saat di-scroll      │
│ • Zebra Striping: Baris genap #FFFFFF, Baris ganjil Soft Ivory #FAFAFA                 │
│ • Cell Number Formatting: Seluruh nominal uang bertipe Number dengan format Rp #,##0   │
│ • Grand Total Row: Background #E5E5EA, Bold, Formula Dinamis =SUM(), Double Underline   │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

### Aturan Teknis Ekspor Excel (PhpSpreadsheet & FastExcel):
1. **Dua Sheet Wajib:** Tidak boleh hanya mengekspor 1 tabel data mentah polos tanpa lembar ringkasan eksekutif.
2. **Format Angka Excel Asli:** Kolom nominal uang wajib disimpan sebagai tipe data `Number` dengan format tampilan `Rp #,##0` atau `#,##0` (dilarang string mentah `"Rp 150.000"` yang rusak saat dijumlahkan di Excel).
3. **Formula Dinamis:** Gunakan formula resmi `=SUM(H5:H100)` atau `=K5-L5` pada baris total dan kalkulasi margin.
4. **Auto-Fit Column Width:** Lebar seluruh kolom disesuaikan otomatis dengan panjang konten terpanjang $+ 3$ karakter padding.
5. **Streaming Data Besar (Zero-Timeout):** Gunakan FastExcel / Chunked Streaming untuk dataset di atas 1.000 baris agar tidak memicu memory limit PHP.

---

## 🖥️ 6. Standar Antarmuka Halaman Laporan Web (Overview + Drill-Down Detail)

Setiap halaman laporan di aplikasi web COOCA wajib menyediakan dua level kedalaman informasi:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        TATA LETAK HALAMAN LAPORAN WEB (UI/UX)                          │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 1. HEADER & TOOLBAR FILTER CEPAT                                                       │
│    • Preset Tanggal: [ Hari Ini ] [ 7 Hari ] [ Bulan Ini ] [ Kuartal ] [ Kustom ]      │
│    • Filter Multi-Dimensi: Cabang/Outlet, Saluran (POS/Online/MP/B2B), Kategori        │
│    • Tombol Aksi: [ 📊 Terapkan Filter ]  [ 📥 Ekspor Excel (XLSX) ]  [ 🖨️ Cetak PDF ]  │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 2. TOP OVERVIEW BAR (BENTO KPI CARDS)                                                  │
│    • 4 Kartu Bento Metrik Utama: Total Nominal, Total Transaksi/Item, Rata-rata, Laba  │
│    • Angka besar tabular-nums dengan indikator pertumbuhan (↑ +12.4% hijau / ↓ -2.1%)  │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 3. TABEL DATA UTAMA DENGAN INTERACTIVE DRILL-DOWN                                      │
│    • Tabel ringkasan transaksi/entitas dengan sorting & pagination cepat               │
│    • Kolom Aksi / Tombol [ 🔍 Rincian Detail ] pada setiap baris data                 │
│    • Modal Drawer / Expandable Row: Menampilkan rincian item, SKU, modal/HPP barang,   │
│      pajak, diskon, catatan kasir, dan jurnal pembukuan debit/kredit tanpa reload page │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 7. Protokol Audit Laporan & Dashboard (5-Tahap)

Saat user meminta audit modul laporan atau dashboard:
1. **Audit Agregasi & Formula:** Verifikasi apakah rumus matematika (Omzet, HPP, Laba Bersih, Selisih Kas, Pajak) sesuai standar akuntansi dan bebas bug pembagian nol.
2. **Audit Konsistensi Sumber Saluran:** Pastikan data POS, Marketplace Hub, Storefront, dan B2B SO tercatat akurat tanpa duplikasi atau data hilang (*orphan transactions*).
3. **Audit Mesin Ekspor Excel (Multi-Sheet & Styling):** Uji tombol ekspor XLSX: pastikan memuat Sheet 1 (Overview) & Sheet 2 (Detail), styling Bento Apple HIG (Dark header, Zebra, Bento KPI box), format angka cell `Rp #,##0`, AutoFilter, Freeze Panes, dan baris Grand Total dengan formula `=SUM()`.
4. **Audit UX Bento & Web Drill-Down Detail:** Pastikan halaman web menyediakan Top KPI Overview Cards dan tabel dengan kemampuan Expandable Row / Modal Drawer untuk melihat rincian item transaksi tanpa reload.
5. **Audit Multi-Bahasa:** Pastikan seluruh header kolom laporan, nama metrik, judul sheet Excel, dan filter tanggal 100% translatable via `lang/id/` & `lang/en/`.
