# Cetak Biru Arsitektur Dashboard Bento Apple HIG per Domain Bisnis

Dokumen ini mendefinisikan rancangan visual, kartu Bento Grid, visualisasi grafik (*charts*), dan integrasi filter waktu untuk **4 Dashboard Analitik Utama di COOCA**.

---

## 🎨 1. Standar Desain Dashboard Bento Apple HIG v2.0

Setiap Dashboard Domain wajib mengikuti hierarki berikut:
1. **Toolbar Header Terpadu:** Breadcrumb domain, Page Title besar (`text-2xl sm:text-3xl font-bold`), preset filter tanggal (`Hari Ini`, `7 Hari`, `Bulan Ini`, `Tahun Ini`, `Kustom`), selector cabang/outlet, dan tombol aksi `[ 📥 Ekspor Excel ]` / `[ 🖨️ Cetak PDF ]`.
2. **Bento Grid Kartu Metrik Utama (Baris 1):** 4 Kartu Bento KPI utama dengan tipografi angka besar (`text-3xl font-bold tabular-nums`), label semantik abu-abu netral, dan perbandingan terhadap periode sebelumnya (*delta growth*: $\uparrow +12.4\%$ hijau / $\downarrow -3.2\%$ merah).
3. **Bento Grid Visualisasi Grafik (Baris 2):** Chart interaktif berbasis Chart.js / SVG (rasio 8:4 kolom di desktop, full-width di mobile).
4. **Bento Grid Rincian Tabel Ringkas (Baris 3):** Top 5 entitas teratas dengan link "Lihat Seluruh Laporan Lengkap $\rightarrow$".

---

## 💰 2. Blueprint Dashboard Keuangan (`/finance/dashboard`)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ HEADER: KEUANGAN & PEMBUKUAN > DASHBOARD KINERJA KEUANGAN                              │
│ Filter: [ Bulan Ini ▾ ] [ Semua Rekening ▾ ]                  [ 📥 Ekspor Laba Rugi ]  │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento KPI 1 ]            [ Bento KPI 2 ]       [ Bento KPI 3 ]       [ Bento KPI 4 ] │
│ TOTAL OMZET BERSIH         LABA KOTOR (HPP)      BEBAN OPERASIONAL     LABA BERSIH (NET)
│ Rp 148.500.000             Rp 59.400.000 (40%)   Rp 28.200.000         Rp 31.200.000   │
│ ↑ +8.5% vs bulan lalu      ↑ +6.2% vs target     ↓ -2.1% efisien       ↑ +14.8% net    │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento Kiri 8-Kolom: Grafik Tren Laba Rugi ]  │ [ Bento Kanan 4-Kolom: Struktur Beban]│
│ • Bar Chart: Omzet vs HPP vs Beban Bulanan     │ • Donut Chart: Gaji (45%), Sewa (25%),│
│ • Line Chart: Margin Laba Bersih Kumulatif     │   Listrik/Air (15%), Lainnya (15%)    │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento Kiri 6-Kolom: Piutang Pelanggan (AR) ] │ [ Bento Kanan 6-Kolom: Hutang Supplier│
│ • Total Piutang: Rp 18.500.000                 │ • Total Hutang PO: Rp 24.100.000      │
│ • Jatuh Tempo < 30 Hari: Rp 14.000.000         │ • Jatuh Tempo Minggu Ini: Rp 8.000.000│
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🛍️ 3. Blueprint Dashboard Penjualan Multisaluran (`/sales/dashboard`)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ HEADER: PENJUALAN & TRANSAKSI > DASHBOARD PENJUALAN OMNICHANNEL                        │
│ Filter: [ 7 Hari Terakhir ▾ ] [ Semua Saluran ▾ ]             [ 📥 Ekspor Penjualan ]  │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento KPI 1 ]            [ Bento KPI 2 ]       [ Bento KPI 3 ]       [ Bento KPI 4 ] │
│ TOTAL PENJUALAN GABUNGAN   TOTAL TRANSAKSI       RATA-RATA NOTA (AOV)  TINGKAT RETUR   │
│ Rp 84.250.000              1.420 Nota            Rp 59.330 / Nota      0.4% (Aman)     │
│ ↑ +14.2% vs minggu lalu    ↑ +120 transaksi      ↑ +4.5% belanja       ↓ -0.2% komplain│
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento Kiri 7-Kolom: Kontribusi per Saluran ] │ [ Bento Kanan 5-Kolom: Jam Ramai POS] │
│ • Multi-Bar Chart per Hari:                    │ • Peak Hours Heatmap (11:00 - 14:00 & │
│   - POS Kasir Offline    : Rp 46.500.000 (55%) │   18:00 - 21:00)                      │
│   - Shopee & Tokopedia   : Rp 22.000.000 (26%) │ • Kasir Terbaik: Siti Aminah (340 tx) │
│   - Toko Online Web      : Rp 10.250.000 (12%) │                                       │
│   - Sales Order B2B      : Rp  5.500.000 ( 7%) │                                       │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento 12-Kolom: Top 5 Produk Terlaris (Leaderboard) ]                                │
│ #1 Kopi Susu Gula Aren (450 cup - Rp 9.000.000) • #2 Croissant Butter (310 pcs) ...   │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 📦 4. Blueprint Dashboard Inventori & Gudang (`/inventory/dashboard`)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ HEADER: KATALOG & GUDANG > DASHBOARD KESEHATAN PERSEDIAAN                              │
│ Filter: [ Gudang Pusat ▾ ] [ Semua Kategori ▾ ]               [ 📥 Ekspor Valuasi ]    │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento KPI 1 ]            [ Bento KPI 2 ]       [ Bento KPI 3 ]       [ Bento KPI 4 ] │
│ TOTAL VALUASI STOK (ASET)  TOTAL SKU AKTIF       SKU KRITIS / HABIS    DAYS OF INVENTORY
│ Rp 342.800.000             1.250 Barang          14 Barang Menipis     24 Hari Cadangan│
│ FIFO Valuation             98% Terdaftar         ⚠️ Butuh PO Segera    Perputaran 15x/th│
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento Kiri 8-Kolom: Persebaran Nilai Stok ]  │ [ Bento Kanan 4-Kolom: Peringatan Low]│
│ • Bar Chart per Kategori / Sub-Gudang          │ • List SKU Kritis (Sisa <= Buffer)    │
│ • Klasifikasi Fast vs Slow Moving              │ • Tombol Quick Action [+ Buat PO PO]  │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento 12-Kolom: Log Mutasi & Penerimaan Barang Terkini ]                             │
│ • 5 Surat Jalan Terakhir • 5 Penyesuaian Opname Terakhir dengan Status Approval        │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 👥 5. Blueprint Dashboard SDM & Penggajian (`/hr/dashboard`)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ HEADER: KARYAWAN & PRESENSI > DASHBOARD KINERJA SDM                                    │
│ Filter: [ Bulan Ini ▾ ] [ Semua Outlet ▾ ]                    [ 📥 Ekspor Payroll ]    │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento KPI 1 ]            [ Bento KPI 2 ]       [ Bento KPI 3 ]       [ Bento KPI 4 ] │
│ TINGKAT KEHADIRAN HARI INI TOTAL KARYAWAN AKTIF  TOTAL BEBAN GAJI BULAN RATIO PAYROLL/OMZET
│ 96.5% (28/29 Hadir)        32 Staf               Rp 64.500.000         18.8% (Sangat Sehat
│ 1 Izin, 0 Alpa             4 Divisi              Termasuk Lembur & BPJS (Batas Ideal < 30%)│
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento Kiri 7-Kolom: Tren Presensi Mingguan ] │ [ Bento Kanan 5-Kolom: Kasbon Staf ]  │
│ • Line Chart: Jam Masuk Tepat Waktu vs Lembur  │ • Total Saldo Kasbon: Rp 4.200.000    │
│ • Geofence Compliance: 100% Dalam Radius       │ • Estimasi Potongan Gaji: Rp 1.500.000│
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Bento 12-Kolom: Produktivitas Staf & Omzet Kasir ]                                   │
│ • Peringkat Omzet per Kasir • Jam Kerja Efektif per Departemen                         │
└────────────────────────────────────────────────────────────────────────────────────────┘
```
