# Matriks Spesifikasi Laporan Bisnis & Standar Ekspor Microsoft Excel (XLSX)

Dokumen ini memuat standar teknis spesifikasi kolom, rumus kalkulasi, arsitektur multi-sheet (Overview & Detail), format numerik, dan tata letak styling profesional Microsoft Excel (XLSX) untuk seluruh laporan di ekosistem COOCA ID & POS v2.0.

---

## 🏛️ 1. Arsitektur Dua Bagian Wajib (Executive Overview & Detailed Ledger)

Setiap proses ekspor data ke Excel **DILARANG HANYA MENGHASILKAN SATU TABEL DATA MENTAH POLOS**. File Excel wajib menerapkan struktur dua bagian yang kaya konteks:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        STRUKTUR WORKBOOK EXCEL DUA BAGIAN                              │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ SHEET 1: RINGKASAN EKSEKUTIF (EXECUTIVE OVERVIEW & KPI)                                │
│ • Header Formal (Nama Usaha, Logo, Tanggal, Outlet, Dicetak Oleh)                      │
│ • Bento KPI Cards Grid (Total Omzet, Total Transaksi, Total HPP, Laba Bersih, AOV)     │
│ • Tabel Agregasi Ringkas (Breakdown per Saluran / per Kategori / per Metode Bayar)     │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ SHEET 2: RINCIAN DATA LENGKAP (TRANSACTIONAL & MOVEMENT LEDGER)                        │
│ • Rincian data baris demi baris (Item-level detail, Nomor Nota, SKU, Jam Transaksi)   │
│ • Filter Dropdown Otomatis (Excel AutoFilter) aktif pada baris header                  │
│ • Freeze Panes (Header terkunci saat di-scroll ke bawah)                               │
│ • Rumus Formula Dinamis (=SUM(), =AVERAGE(), =SUBTOTAL())                              │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🎨 2. Standar Visual Styling Excel (Bento Apple HIG Palette)

File Excel wajib distyling menggunakan palet warna dan tipografi profesional:

| Elemen Excel | Warna Background | Warna Font & Style | Border & Garis |
|---|---|---|---|
| **Header Utama Laporan** | Dark Onyx `#1C1C1E` atau Apple Blue `#007AFF` | Putih `#FFFFFF`, Bold, 11pt, Center | Hairline Border `#E5E5EA` |
| **Kartu Bento KPI (Sheet 1)** | Soft Gray `#F2F2F7` | Hitam `#000000`, Angka Bold 14pt, Label 9pt | Rounded Hairline `#D1D1D6` |
| **Baris Data Genap** | Putih Bersih `#FFFFFF` | Hitam `#1C1C1E`, Regular, 10pt | Garis tipis `#F2F2F7` |
| **Baris Data Ganjil (Zebra)**| Soft Ivory `#FAFAFA` | Hitam `#1C1C1E`, Regular, 10pt | Garis tipis `#F2F2F7` |
| **Status Positif (Laba/Aman)**| Soft Green `#34C759`/15% | Hijau `#248A3D`, Bold 9pt | Green border `#34C759`/30% |
| **Status Negatif (Rugi/Kritis)**| Soft Red `#FF3B30`/15% | Merah `#C41E17`, Bold 9pt | Red border `#FF3B30`/30% |
| **Baris Grand Total** | Dark Gray `#E5E5EA` | Hitam `#000000`, Bold 11pt, Formula `=SUM()` | Double Underline Bawah |

---

## 📊 3. Matriks Spesifikasi Laporan per Domain Bisnis

### A. Domain Keuangan & Pembukuan (`finance`)
| Nama Laporan | Konten Sheet 1 (Overview) | Konten Sheet 2 (Detail Ledger) | Format Numerik & Formula |
|---|---|---|---|
| **Laba Rugi (P&L)** | Kartu KPI: Total Omzet, Total HPP, Laba Kotor, Total Beban, Laba Bersih. Tabel Ringkasan per Kategori Beban. | Rincian seluruh akun pendapatan, akun HPP bahan baku, dan beban operasional buku besar. | Currency `Rp #,##0`, Margin Persentase `0.0%`, Formula `=SUM()` |
| **Neraca (Balance Sheet)** | Kartu KPI: Total Aset, Total Kewajiban (Hutang), Total Ekuitas Modal. Status Balance ($\Delta = 0$). | Rincian per sub-akun kas/bank, piutang pelanggan, persediaan barang, hutang supplier, dan laba ditahan. | Currency `Rp #,##0`, Double-Underline Total |
| **Arus Kas (Cash Flow)** | Kartu KPI: Kas Masuk, Kas Keluar, Arus Kas Bersih Operasional, Saldo Kas Akhir. | Rincian mutasi kas masuk/keluar per tanggal, no. referensi, akun lawan, dan keterangan transaksi. | Currency `Rp #,##0`, Angka Minus bertanda `(Rp #,##0)` |
| **Buku Kas & Bank** | Kartu KPI: Saldo Awal, Total Penerimaan, Total Pengeluaran, Saldo Akhir per Rekening. | Rincian mutasi kasir harian, setor tunai, transfer bank, fee MDR QRIS, rekonsiliasi. | Currency `Rp #,##0`, Running Balance Formula |

---

### B. Domain Penjualan Multisaluran (`sales` / `pos` / `marketplace`)
| Nama Laporan | Konten Sheet 1 (Overview) | Konten Sheet 2 (Detail Ledger) | Format Numerik & Formula |
|---|---|---|---|
| **Penjualan Multisaluran** | Kartu KPI: Total Omzet Bersih, Total Transaksi, AOV, Total Margin HPP.<br>Tabel Breakdown: POS Offline vs Toko Online vs Shopee vs Tokopedia vs B2B SO. | Rincian per transaksi: Tanggal, Jam, No. Nota, Saluran, Kasir/Sales, Pelanggan, Subtotal, Diskon, Pajak, Total, HPP, Margin (Rp), Metode Bayar. | Currency `Rp #,##0`, Qty `#,#00`, Text Uppercase untuk Channel, AutoFilter Aktif |
| **Ringkasan Shift Kasir** | Kartu KPI: Total Shift Selesai, Total Setoran Kas, Total Selisih Kas (Variance), Kasir Terbaik. | Rincian per sesi shift: ID Shift, Tanggal Buka/Tutup, Nama Kasir, Kas Awal, Kas Masuk Tunai, Non-Tunai, Kas Fisik Dihitung, Selisih Kas, Status. | Currency `Rp #,##0`, Cell Selisih berwarna Merah jika minus |
| **Produk Terlaris (Top SKU)**| Kartu KPI: Total Unit Terjual, SKU Paling Laris, Kategori Kontributor Omzet Terbesar. | Peringkat SKU, Barcode, Nama Produk, Kategori, Qty Terjual, Satuan, Total Omzet, Total HPP, Laba Kotor, Kontribusi Omzet (%). | Number `#,##0`, Currency `Rp #,##0`, Persentase `0.0%` |

---

### C. Domain Inventori & Gudang (`inventory` / `warehouse`)
| Nama Laporan | Konten Sheet 1 (Overview) | Konten Sheet 2 (Detail Ledger) | Format Numerik & Formula |
|---|---|---|---|
| **Valuasi Persediaan** | Kartu KPI: Total Nilai Aset Stok (Rp), Total SKU Terdaftar, SKU Kritis, Estimasi Cadangan Hari (*Days on Hand*). Tabel per Gudang. | Rincian per item: SKU, Barcode, Nama Barang, Kategori, Gudang/Cabang, Sisa Stok Fisik, Satuan, Harga Pokok Rata-Rata (Avg Cost), Total Nilai Stok (Rp). | Number `#,##0.00`, Currency `Rp #,##0`, Grand Total Nilai Persediaan |
| **Mutasi Stok (Movement)** | Kartu KPI: Total Qty Masuk (PO/GR), Total Qty Keluar (POS/Sales/BOM), Total Penyesuaian Opname. | Rincian mutasi: Tanggal, Waktu, No. Ref, SKU, Nama Barang, Gudang, Tipe Mutasi, Stok Awal, Qty Mutasi, Stok Akhir, Petugas, Catatan. | Number `#,##0`, Running Balance Per SKU |
| **Stok Kritis & Menipis** | Kartu KPI: Jumlah SKU Habis (0), Jumlah SKU Menipis, Estimasi Biaya Reorder PO Pengadaan (Rp). | Daftar item: SKU, Nama Barang, Gudang, Sisa Stok Fisik, Batas Minimum Buffer, Status Kritis, Rekomendasi Qty Pesan, Supplier Utama. | Number `#,##0`, Highlight Cell Kuning/Merah |
| **Selisih Stok Opname** | Kartu KPI: Total Nilai Selisih Rugi (Minus), Total Nilai Selisih Untung (Plus), Akurasi Stok (%). | Rincian opname: Tanggal, No. Dokumen, SKU, Nama Barang, Stok Sistem, Stok Fisik Nyata, Selisih Qty, HPP Satuan, Nilai Selisih (Rp), Petugas, Approval. | Number `#,##0`, Currency `Rp #,##0` |

---

### D. Domain SDM & Penggajian (`hr` / `payroll`)
| Nama Laporan | Konten Sheet 1 (Overview) | Konten Sheet 2 (Detail Ledger) | Format Numerik & Formula |
|---|---|---|---|
| **Rekapitulasi Presensi** | Kartu KPI: Tingkat Kehadiran (%), Total Karyawan, Rata-rata Keterlambatan, Total Jam Lembur. | Rincian per staf: NIK, Nama, Divisi, Outlet, Hari Kerja, Hadir Tepat Waktu, Terlambat (Menit), Izin, Sakit, Alpa, Total Jam Lembur, % Kehadiran. | Number `#,##0`, Persentase `0.0%`, Format Jam `[h]:mm` |
| **Rekapitulasi Gaji (Payroll)**| Kartu KPI: Total Beban Gaji Bersih (THP), Total Potongan Kasbon, Total PPh 21 TER, Rasio Payroll/Omzet. | Rincian per staf: NIK, Nama, Jabatan, Gaji Pokok, Tunjangan, Uang Lembur, Komisi, Gaji Bruto, Potongan Kasbon, BPJS, PPh 21, Total Gaji Bersih (THP). | Currency `Rp #,##0`, Baris Grand Total Pengeluaran Gaji |
| **Pajak PPh 21 Karyawan** | Kartu KPI: Total Pajak PPh 21 Disetor, Jumlah Karyawan Wajib Pajak. | Rincian: NIK, Nama, NPWP/NIK, Status PTKP, Gaji Bruto Bulanan, Kategori TER (A/B/C), Tarif TER (%), Potongan Pajak PPh 21. | Currency `Rp #,##0`, Persentase `0.00%` |

---

## 💻 4. Template Script PhpSpreadsheet / FastExcel Multi-Sheet Lengkap

Berikut adalah cetak biru kode backend Laravel untuk menghasilkan workbook Excel multi-sheet berstandar Bento Apple HIG:

```php
namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OmnichannelSalesMultiSheetExport
{
    public static function generate($business, $startDate, $endDate, $summaryKPI, $channelBreakdown, $detailedOrders): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        
        // ==========================================
        // SHEET 1: RINGKASAN EKSEKUTIF & KPI
        // ==========================================
        $sheetOverview = $spreadsheet->getActiveSheet();
        $sheetOverview->setTitle('Ringkasan Eksekutif');
        $sheetOverview->setShowGridLines(true);

        // Header Title Block
        $sheetOverview->setCellValue('A1', strtoupper($business->name));
        $sheetOverview->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1C1C1E'));
        
        $sheetOverview->setCellValue('A2', 'RINGKASAN EKSEKUTIF PENJUALAN MULTISALURAN');
        $sheetOverview->getStyle('A2')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('007AFF'));
        
        $sheetOverview->setCellValue('A3', "Periode: {$startDate} s/d {$endDate} | Dicetak: " . now()->translatedFormat('d F Y H:i') . " WIB");
        $sheetOverview->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('8E8E93');

        // KPI Metric Cards (Row 5 - 7)
        $kpis = [
            ['col_start' => 'A', 'col_end' => 'B', 'label' => 'TOTAL PENJUALAN', 'val' => $summaryKPI['total_sales'], 'format' => 'Rp #,##0'],
            ['col_start' => 'C', 'col_end' => 'D', 'label' => 'TOTAL TRANSAKSI', 'val' => $summaryKPI['total_tx'], 'format' => '#,##0'],
            ['col_start' => 'E', 'col_end' => 'F', 'label' => 'RATA-RATA NOTA (AOV)', 'val' => $summaryKPI['aov'], 'format' => 'Rp #,##0'],
            ['col_start' => 'G', 'col_end' => 'H', 'label' => 'TOTAL LABA KOTOR', 'val' => $summaryKPI['total_margin'], 'format' => 'Rp #,##0'],
        ];

        foreach ($kpis as $kpi) {
            $sheetOverview->mergeCells("{$kpi['col_start']}5:{$kpi['col_end']}5");
            $sheetOverview->mergeCells("{$kpi['col_start']}6:{$kpi['col_end']}6");
            
            $sheetOverview->setCellValue("{$kpi['col_start']}5", $kpi['label']);
            $sheetOverview->getStyle("{$kpi['col_start']}5")->getFont()->setSize(8)->setBold(true)->getColor()->setRGB('8E8E93');
            
            $sheetOverview->setCellValue("{$kpi['col_start']}6", $kpi['val']);
            $sheetOverview->getStyle("{$kpi['col_start']}6")->getFont()->setSize(13)->setBold(true);
            $sheetOverview->getStyle("{$kpi['col_start']}6")->getNumberFormat()->setFormatCode($kpi['format']);
            
            // Bento Card Box Styling
            $sheetOverview->getStyle("{$kpi['col_start']}5:{$kpi['col_end']}6")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F7']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D1D6']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        // Breakdown Table per Saluran (Row 9)
        $sheetOverview->setCellValue('A9', 'KONTRIBUSI PENJUALAN PER SALURAN');
        $sheetOverview->getStyle('A9')->getFont()->setBold(true)->setSize(10);
        
        $channelHeaders = ['Saluran Penjualan', 'Jumlah Transaksi', 'Total Omzet (Rp)', 'Kontribusi (%)'];
        $colIdx = 'A';
        foreach ($channelHeaders as $h) {
            $sheetOverview->setCellValue("{$colIdx}10", $h);
            $colIdx++;
        }
        $sheetOverview->getStyle('A10:D10')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1C1C1E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $rowC = 11;
        foreach ($channelBreakdown as $cb) {
            $sheetOverview->setCellValue("A{$rowC}", $cb['channel_name']);
            $sheetOverview->setCellValue("B{$rowC}", $cb['transaction_count']);
            $sheetOverview->setCellValue("C{$rowC}", $cb['total_omzet']);
            $sheetOverview->setCellValue("D{$rowC}", $cb['percentage'] / 100);
            
            $sheetOverview->getStyle("B{$rowC}")->getNumberFormat()->setFormatCode('#,##0');
            $sheetOverview->getStyle("C{$rowC}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $sheetOverview->getStyle("D{$rowC}")->getNumberFormat()->setFormatCode('0.0%');
            $rowC++;
        }
        
        foreach (range('A', 'H') as $col) {
            $sheetOverview->getColumnDimension($col)->setAutoSize(true);
        }

        // ==========================================
        // SHEET 2: RINCIAN TRANSAKSI DETAIL
        // ==========================================
        $sheetDetail = $spreadsheet->createSheet();
        $sheetDetail->setTitle('Rincian Data Lengkap');
        $sheetDetail->setShowGridLines(true);

        // Header Title Block
        $sheetDetail->setCellValue('A1', strtoupper($business->name) . " - RINCIAN TRANSAKSI PENJUALAN");
        $sheetDetail->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheetDetail->setCellValue('A2', "Periode: {$startDate} s/d {$endDate} | Total Data: " . count($detailedOrders) . " baris");
        $sheetDetail->getStyle('A2')->getFont()->setSize(9)->getColor()->setRGB('8E8E93');

        // Table Header (Row 4)
        $detailHeaders = ['No', 'Tanggal', 'Jam', 'No. Transaksi', 'Saluran', 'Pelanggan', 'Kasir / Sales', 'Subtotal (Rp)', 'Diskon (Rp)', 'Pajak (Rp)', 'Total Bersih (Rp)', 'HPP Modal (Rp)', 'Laba Kotor (Rp)', 'Metode Bayar', 'Status'];
        $colChar = 'A';
        foreach ($detailHeaders as $dh) {
            $sheetDetail->setCellValue("{$colChar}4", $dh);
            $colChar++;
        }
        $lastCol = chr(ord('A') + count($detailHeaders) - 1);
        
        $sheetDetail->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1C1C1E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E5EA']]],
        ]);
        $sheetDetail->getRowDimension(4)->setRowHeight(26);

        // Freeze Panes & AutoFilter
        $sheetDetail->freezePane('A5');
        $sheetDetail->setAutoFilter("A4:{$lastCol}4");

        // Populate Data Rows with Zebra Striping
        $rowD = 5;
        $no = 1;
        foreach ($detailedOrders as $o) {
            $sheetDetail->setCellValue("A{$rowD}", $no++);
            $sheetDetail->setCellValue("B{$rowD}", $o->date);
            $sheetDetail->setCellValue("C{$rowD}", $o->time);
            $sheetDetail->setCellValue("D{$rowD}", $o->order_number);
            $sheetDetail->setCellValue("E{$rowD}", strtoupper($o->sales_channel));
            $sheetDetail->setCellValue("F{$rowD}", $o->customer_name ?? 'Umum');
            $sheetDetail->setCellValue("G{$rowD}", $o->cashier_name ?? '-');
            $sheetDetail->setCellValue("H{$rowD}", $o->subtotal);
            $sheetDetail->setCellValue("I{$rowD}", $o->discount_amount);
            $sheetDetail->setCellValue("J{$rowD}", $o->tax_amount);
            $sheetDetail->setCellValue("K{$rowD}", $o->total_amount);
            $sheetDetail->setCellValue("L{$rowD}", $o->total_cogs);
            $sheetDetail->setCellValue("M{$rowD}", "=K{$rowD}-L{$rowD}"); // Dynamic Formula Margin
            $sheetDetail->setCellValue("N{$rowD}", strtoupper($o->payment_method));
            $sheetDetail->setCellValue("O{$rowD}", strtoupper($o->status));

            // Formatting
            $sheetDetail->getStyle("H{$rowD}:M{$rowD}")->getNumberFormat()->setFormatCode('Rp #,##0');
            
            // Zebra Background
            if ($rowD % 2 === 0) {
                $sheetDetail->getStyle("A{$rowD}:{$lastCol}{$rowD}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FAFAFA');
            }
            $rowD++;
        }

        // Grand Total Row with Formulas
        $lastDataRow = $rowD - 1;
        $sheetDetail->setCellValue("A{$rowD}", 'GRAND TOTAL');
        $sheetDetail->setCellValue("H{$rowD}", "=SUM(H5:H{$lastDataRow})");
        $sheetDetail->setCellValue("I{$rowD}", "=SUM(I5:I{$lastDataRow})");
        $sheetDetail->setCellValue("J{$rowD}", "=SUM(J5:J{$lastDataRow})");
        $sheetDetail->setCellValue("K{$rowD}", "=SUM(K5:K{$lastDataRow})");
        $sheetDetail->setCellValue("L{$rowD}", "=SUM(L5:L{$lastDataRow})");
        $sheetDetail->setCellValue("M{$rowD}", "=SUM(M5:M{$lastDataRow})");

        $sheetDetail->getStyle("A{$rowD}:{$lastCol}{$rowD}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E5EA']],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE],
            ],
        ]);
        $sheetDetail->getStyle("H{$rowD}:M{$rowD}")->getNumberFormat()->setFormatCode('Rp #,##0');

        foreach (range('A', $lastCol) as $col) {
            $sheetDetail->getColumnDimension($col)->setAutoSize(true);
        }

        // Default to Sheet 1
        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }
}
```
