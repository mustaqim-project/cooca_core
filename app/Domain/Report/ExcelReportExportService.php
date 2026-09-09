<?php

declare(strict_types=1);

namespace App\Domain\Report;

use App\Models\Business;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExcelReportExportService
{
    private const COLOR_HEADER_BG = '0F172A'; // Slate 900 / Navy
    private const COLOR_HEADER_FG = 'FFFFFF';
    private const COLOR_ACCENT_BG = '065F46'; // Deep Emerald
    private const COLOR_ACCENT_CARD = 'ECFDF5'; // Light Mint
    private const COLOR_CARD_BORDER = '10B981'; // Emerald 500
    private const COLOR_SUBHEADER_BG = 'F1F5F9'; // Slate 100
    private const COLOR_ZEBRA_BG = 'F8FAFC'; // Slate 50
    private const COLOR_TOTAL_BG = 'E2E8F0'; // Slate 200
    private const COLOR_ALERT_BG = 'FEF2F2'; // Rose 50
    private const COLOR_ALERT_BORDER = 'F43F5E'; // Rose 500

    public function generateComprehensiveWorkbook(
        Business $business,
        Carbon $startDate,
        Carbon $endDate,
        array $incomeStatement,
        array $cashFlow,
        array $agingSummary,
        array $stockValuation,
        array $hppReport,
        array $costBreakdown
    ): Spreadsheet {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($business->name . ' - Cooca Business Suite')
            ->setLastModifiedBy($business->name)
            ->setTitle('Laporan Bisnis Komprehensif')
            ->setSubject('Laporan Finansial & Operasional')
            ->setDescription('Laporan Komprehensif Laba Rugi, Arus Kas, Valuasi Stok, Piutang, Hutang, dan HPP');

        // Sheet 1: Executive Dashboard
        $sheetDashboard = $spreadsheet->getActiveSheet();
        $sheetDashboard->setTitle('Dashboard');
        $this->buildDashboardSheet(
            $sheetDashboard,
            $business,
            $startDate,
            $endDate,
            $incomeStatement,
            $cashFlow,
            $agingSummary,
            $stockValuation
        );

        // Sheet 2: Laba Rugi (P&L)
        $sheetPL = $spreadsheet->createSheet();
        $sheetPL->setTitle('Laba Rugi (P&L)');
        $this->buildIncomeStatementSheet($sheetPL, $business, $incomeStatement);

        // Sheet 3: Arus Kas (Cash Flow)
        $sheetCF = $spreadsheet->createSheet();
        $sheetCF->setTitle('Arus Kas');
        $this->buildCashFlowSheet($sheetCF, $business, $cashFlow);

        // Sheet 4: Valuasi & Mutasi Stok
        $sheetStock = $spreadsheet->createSheet();
        $sheetStock->setTitle('Valuasi Stok');
        $this->buildStockValuationSheet($sheetStock, $business, $stockValuation);

        // Sheet 5: Piutang & Hutang (AR & AP)
        $sheetAging = $spreadsheet->createSheet();
        $sheetAging->setTitle('Piutang & Hutang');
        $this->buildAgingSheet($sheetAging, $business, $agingSummary);

        // Sheet 6: HPP & Margin Produk
        $sheetHpp = $spreadsheet->createSheet();
        $sheetHpp->setTitle('HPP & Margin Produk');
        $this->buildHppSheet($sheetHpp, $business, $hppReport, $costBreakdown);

        // Set active sheet back to Dashboard
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public function downloadComprehensiveWorkbook(
        Business $business,
        Carbon $startDate,
        Carbon $endDate,
        array $incomeStatement,
        array $cashFlow,
        array $agingSummary,
        array $stockValuation,
        array $hppReport,
        array $costBreakdown
    ): StreamedResponse {
        $spreadsheet = $this->generateComprehensiveWorkbook(
            $business,
            $startDate,
            $endDate,
            $incomeStatement,
            $cashFlow,
            $agingSummary,
            $stockValuation,
            $hppReport,
            $costBreakdown
        );

        $filename = 'Laporan_Komprehensif_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $business->name) . '_' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.xlsx';

        return new StreamedResponse(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    // =========================================================================
    // SHEET 1: EXECUTIVE DASHBOARD
    // =========================================================================
    private function buildDashboardSheet(
        Worksheet $sheet,
        Business $business,
        Carbon $startDate,
        Carbon $endDate,
        array $incomeStatement,
        array $cashFlow,
        array $agingSummary,
        array $stockValuation
    ): void {
        $sheet->setShowGridLines(true);

        // Header Title Block
        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'EXECUTIVE DASHBOARD & LAPORAN OPERASIONAL BISNIS');
        $sheet->setCellValue('B4', 'Periode: ' . $startDate->translatedFormat('d F Y') . ' s/d ' . $endDate->translatedFormat('d F Y') . ' | Digenerate: ' . now()->translatedFormat('d F Y H:i'));

        $sheet->getStyle('B2')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $sheet->getStyle('B3')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB(self::COLOR_HEADER_BG);
        $sheet->getStyle('B4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        // Extract KPIs
        $netSales = (float) ($incomeStatement['net_sales'] ?? 0);
        $totalCogs = (float) ($incomeStatement['cogs']['total_cogs'] ?? 0);
        $grossProfit = (float) ($incomeStatement['gross_profit'] ?? 0);
        $grossMarginPct = (float) ($incomeStatement['gross_profit_margin'] ?? 0);
        $totalExpenses = (float) ($incomeStatement['operating_expenses']['total'] ?? 0);
        $netProfit = (float) ($incomeStatement['net_profit'] ?? 0);
        $netMarginPct = (float) ($incomeStatement['net_profit_margin'] ?? 0);

        $netCashFlow = (float) ($cashFlow['summary']['net_cash_flow'] ?? 0);
        $closingCash = (float) ($cashFlow['summary']['closing_balance'] ?? 0);

        $totalStockVal = (float) ($stockValuation['summary']['total_valuation'] ?? 0);
        $totalUnits = (float) ($stockValuation['summary']['total_physical_units'] ?? 0);

        $totalAr = (float) ($agingSummary['ar']['total_receivables'] ?? 0);
        $arOverdue = (float) ($agingSummary['ar']['total_overdue'] ?? 0);
        $totalAp = (float) ($agingSummary['ap']['total_payables'] ?? 0);
        $apOverdue = (float) ($agingSummary['ap']['total_overdue'] ?? 0);

        // Row 6-9: KPI CARDS ROW 1 (4 Cards across cols B to I)
        // Card 1: Penjualan Bersih (Cols B-C)
        $this->renderKpiCard($sheet, 'B', 'C', 6, 'TOTAL PENJUALAN BERSIH', $netSales, 'Omzet riil POS & Faktur', self::COLOR_ACCENT_CARD, self::COLOR_CARD_BORDER, 'Rp #,##0');
        // Card 2: Laba Kotor & Margin (Cols D-E)
        $this->renderKpiCard($sheet, 'D', 'E', 6, 'LABA KOTOR (GROSS PROFIT)', $grossProfit, 'Margin: ' . number_format($grossMarginPct, 1) . '%', 'F0FDF4', '22C55E', 'Rp #,##0');
        // Card 3: Beban Operasional (Cols F-G)
        $this->renderKpiCard($sheet, 'F', 'G', 6, 'TOTAL BEBAN OPERASIONAL', $totalExpenses, 'Beban toko & overhead', 'FFFBEB', 'F59E0B', 'Rp #,##0');
        // Card 4: Estimasi Laba Bersih (Cols H-I)
        $this->renderKpiCard($sheet, 'H', 'I', 6, 'ESTIMASI LABA BERSIH (NET)', $netProfit, 'Net Margin: ' . number_format($netMarginPct, 1) . '%', $netProfit >= 0 ? 'F0FDF4' : 'FEF2F2', $netProfit >= 0 ? '16A34A' : 'EF4444', 'Rp #,##0');

        // Row 11-14: KPI CARDS ROW 2 (4 Cards across cols B to I)
        // Card 5: Arus Kas Bersih
        $this->renderKpiCard($sheet, 'B', 'C', 11, 'ARUS KAS BERSIH (NET CASH)', $netCashFlow, 'Saldo Akhir: Rp ' . number_format($closingCash, 0, ',', '.'), $netCashFlow >= 0 ? 'F0FDF4' : 'FEF2F2', '0284C7', 'Rp #,##0');
        // Card 6: Valuasi Aset Stok
        $this->renderKpiCard($sheet, 'D', 'E', 11, 'VALUASI ASET PERSEDIAAN', $totalStockVal, number_format($totalUnits, 0, ',', '.') . ' unit fisik stok', 'EFF6FF', '3B82F6', 'Rp #,##0');
        // Card 7: Total Piutang Usaha (AR)
        $this->renderKpiCard($sheet, 'F', 'G', 11, 'PIUTANG USAHA (AR BELUM LUNAS)', $totalAr, 'Jatuh tempo: Rp ' . number_format($arOverdue, 0, ',', '.'), $arOverdue > 0 ? 'FFF1F2' : 'F8FAFC', 'E11D48', 'Rp #,##0');
        // Card 8: Total Hutang Usaha (AP)
        $this->renderKpiCard($sheet, 'H', 'I', 11, 'HUTANG SUPPLIER (AP BELUM LUNAS)', $totalAp, 'Jatuh tempo: Rp ' . number_format($apOverdue, 0, ',', '.'), $apOverdue > 0 ? 'FFF7ED' : 'F8FAFC', 'EA580C', 'Rp #,##0');

        // Row 16: EXECUTIVE HIGHLIGHT TABLES
        // Left Table: Komposisi Finansial (Cols B-E)
        $sheet->setCellValue('B16', 'RINGKASAN EKSEKUTIF KEUANGAN (P&L & CASH FLOW)');
        $sheet->mergeCells('B16:E16');
        $this->styleSectionHeader($sheet, 'B16:E16');

        $plRows = [
            ['Total Penjualan Kotor (Gross)', (float) ($incomeStatement['gross_sales'] ?? 0), '100.0%'],
            ['Potongan & Diskon Penjualan', (float) ($incomeStatement['discounts'] ?? 0), number_format($netSales > 0 ? (($incomeStatement['discounts'] ?? 0) / $netSales) * 100 : 0, 1) . '%'],
            ['Retur Penjualan Konsumen', (float) ($incomeStatement['returns'] ?? 0), '-'],
            ['Pendapatan Penjualan Bersih (Net)', $netSales, '100.0%'],
            ['Beban Pokok Penjualan (HPP)', $totalCogs, number_format($netSales > 0 ? ($totalCogs / $netSales) * 100 : 0, 1) . '%'],
            ['Laba Kotor (Gross Profit)', $grossProfit, number_format($grossMarginPct, 1) . '%'],
            ['Total Biaya & Beban Operasional', $totalExpenses, number_format($netSales > 0 ? ($totalExpenses / $netSales) * 100 : 0, 1) . '%'],
            ['Estimasi Laba Bersih Usaha', $netProfit, number_format($netMarginPct, 1) . '%'],
            ['Arus Kas Masuk Operasional', (float) ($cashFlow['operating_activities']['cash_in']['total'] ?? 0), '-'],
            ['Arus Kas Keluar Operasional', (float) ($cashFlow['operating_activities']['cash_out']['total'] ?? 0), '-'],
            ['Arus Kas Bersih Periode Ini', $netCashFlow, '-'],
            ['Saldo Akhir Kas & Bank Tersedia', $closingCash, '-'],
        ];

        $r = 17;
        foreach ($plRows as $idx => $row) {
            $sheet->setCellValue("B{$r}", $row[0]);
            $sheet->mergeCells("B{$r}:C{$r}");
            $sheet->setCellValue("D{$r}", $row[1]);
            $sheet->setCellValue("E{$r}", $row[2]);

            $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $isHighlight = in_array($idx, [3, 5, 7, 10, 11]);
            if ($isHighlight) {
                $sheet->getStyle("B{$r}:E{$r}")->getFont()->setBold(true);
                $sheet->getStyle("B{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            }
            $this->applyBorderThin($sheet, "B{$r}:E{$r}");
            $r++;
        }

        // Right Table: PANDUAN NAVIGASI WORKBOOK & INDEKS SHEET (Cols G-I)
        $sheet->setCellValue('G16', 'PANDUAN LEMBAR KERJA OPERASIONAL (SHEET INDEX)');
        $sheet->mergeCells('G16:I16');
        $this->styleSectionHeader($sheet, 'G16:I16');

        $navRows = [
            ['Laba Rugi (P&L)', 'Rincian Penjualan POS & Faktur, HPP per item, dan seluruh pos beban operasional.'],
            ['Arus Kas', 'Mutasi uang masuk & keluar riil, pelunasan faktur, dan saldo kas/bank.'],
            ['Valuasi Stok', 'Daftar semua produk, kuantitas stok fisik, modal WAC, status fast/slow moving.'],
            ['Piutang & Hutang', 'Analisis umur tagihan (Aging 0-90+ hari), daftar faktur klien & hutang supplier.'],
            ['HPP & Margin Produk', 'Analisis HPP resep/BOM per unit produk (Bahan, Tenaga Kerja, Mesin, Overhead).'],
        ];

        $nr = 17;
        foreach ($navRows as $n) {
            $sheet->setCellValue("G{$nr}", $n[0]);
            $sheet->setCellValue("H{$nr}", $n[1]);
            $sheet->mergeCells("H{$nr}:I{$nr}");

            $sheet->getStyle("G{$nr}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
            $sheet->getStyle("H{$nr}")->getFont()->setSize(9)->getColor()->setRGB('475569');
            $sheet->getStyle("G{$nr}:I{$nr}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $this->applyBorderThin($sheet, "G{$nr}:I{$nr}");
            $nr++;
        }

        // Auto-size columns
        foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
    }

    // =========================================================================
    // SHEET 2: LABA RUGI (INCOME STATEMENT)
    // =========================================================================
    private function buildIncomeStatementSheet(Worksheet $sheet, Business $business, array $data): void
    {
        $sheet->setShowGridLines(true);

        // Header Title
        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'LAPORAN LABA RUGI KOMPREHENSIF (INCOME STATEMENT)');
        $sheet->setCellValue('B4', 'Periode: ' . ($data['period']['label'] ?? '-'));
        $sheet->getStyle('B2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $sheet->getStyle('B3')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('B4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $r = 6;
        // Table Header
        $sheet->setCellValue("B{$r}", 'Deskripsi Komponen Keuangan');
        $sheet->setCellValue("C{$r}", 'Rincian / Subtotal');
        $sheet->setCellValue("D{$r}", 'Total (Rp)');
        $sheet->setCellValue("E{$r}", '% Terhadap Penjualan');
        $this->styleTableHeader($sheet, "B{$r}:E{$r}");
        $r++;

        $netSales = (float) ($data['net_sales'] ?? 0);

        // 1. PENDAPATAN PENJUALAN
        $sheet->setCellValue("B{$r}", '1. PENDAPATAN OPERASIONAL DARI PENJUALAN');
        $sheet->mergeCells("B{$r}:E{$r}");
        $sheet->getStyle("B{$r}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $r++;

        $posGross = (float) ($data['revenue']['pos']['gross'] ?? 0);
        $posDisc = (float) ($data['revenue']['pos']['discounts'] ?? 0);
        $posNet = (float) ($data['revenue']['pos']['net'] ?? 0);

        $invGross = (float) ($data['revenue']['invoices']['gross'] ?? 0);
        $invDisc = (float) ($data['revenue']['invoices']['discounts'] ?? 0);
        $invNet = (float) ($data['revenue']['invoices']['net'] ?? 0);

        $returns = (float) ($data['revenue']['returns'] ?? 0);

        $this->renderReportRow($sheet, $r++, '   Penjualan Kasir POS (Gross)', $posGross, null, $netSales);
        $this->renderReportRow($sheet, $r++, '   Diskon & Potongan POS', -$posDisc, null, $netSales);
        $this->renderReportRow($sheet, $r++, '   Penjualan Kasir POS Bersih', null, $posNet, $netSales, true);
        $this->renderReportRow($sheet, $r++, '   Penjualan Faktur Klien B2B (Gross)', $invGross, null, $netSales);
        $this->renderReportRow($sheet, $r++, '   Diskon Faktur Klien', -$invDisc, null, $netSales);
        $this->renderReportRow($sheet, $r++, '   Penjualan Faktur Bersih', null, $invNet, $netSales, true);
        if ($returns > 0) {
            $this->renderReportRow($sheet, $r++, '   Retur & Pengembalian Barang Penjualan', -$returns, -$returns, $netSales);
        }

        // Subtotal Net Sales
        $sheet->setCellValue("B{$r}", 'TOTAL PENDAPATAN PENJUALAN BERSIH (NET SALES)');
        $sheet->setCellValue("D{$r}", $netSales);
        $sheet->setCellValue("E{$r}", '100.0%');
        $this->styleSubtotalRow($sheet, "B{$r}:E{$r}");
        $r += 2;

        // 2. BEBAN POKOK PENJUALAN (HPP)
        $sheet->setCellValue("B{$r}", '2. BEBAN POKOK PENJUALAN (COST OF GOODS SOLD / HPP)');
        $sheet->mergeCells("B{$r}:E{$r}");
        $sheet->getStyle("B{$r}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $r++;

        $cogsPos = (float) ($data['cogs']['pos'] ?? 0);
        $cogsInv = (float) ($data['cogs']['invoices'] ?? 0);
        $cogsRet = (float) ($data['cogs']['returns_recovery'] ?? 0);
        $totalCogs = (float) ($data['cogs']['total_cogs'] ?? 0);

        $this->renderReportRow($sheet, $r++, '   HPP Transaksi Kasir POS', $cogsPos, null, $netSales);
        $this->renderReportRow($sheet, $r++, '   HPP Transaksi Faktur Klien', $cogsInv, null, $netSales);
        if ($cogsRet > 0) {
            $this->renderReportRow($sheet, $r++, '   Pemulihan HPP dari Retur Barang', -$cogsRet, null, $netSales);
        }
        $sheet->setCellValue("B{$r}", 'TOTAL BEBAN POKOK PENJUALAN (HPP)');
        $sheet->setCellValue("D{$r}", $totalCogs);
        $sheet->setCellValue("E{$r}", $netSales > 0 ? ($totalCogs / $netSales) : 0);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
        $this->styleSubtotalRow($sheet, "B{$r}:E{$r}");
        $r += 2;

        // 3. LABA KOTOR
        $grossProfit = (float) ($data['gross_profit'] ?? 0);
        $sheet->setCellValue("B{$r}", 'LABA KOTOR (GROSS PROFIT)');
        $sheet->setCellValue("D{$r}", $grossProfit);
        $sheet->setCellValue("E{$r}", $netSales > 0 ? ($grossProfit / $netSales) : 0);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
        $this->styleTotalRow($sheet, "B{$r}:E{$r}");
        $r += 2;

        // 4. BEBAN OPERASIONAL
        $sheet->setCellValue("B{$r}", '3. BEBAN OPERASIONAL & OVERHEAD');
        $sheet->mergeCells("B{$r}:E{$r}");
        $sheet->getStyle("B{$r}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $r++;

        $expCats = $data['operating_expenses']['categories'] ?? [];
        foreach ($expCats as $cat => $amount) {
            $this->renderReportRow($sheet, $r++, '   ' . ucwords($cat), (float) $amount, null, $netSales);
        }
        $totalExp = (float) ($data['operating_expenses']['total'] ?? 0);
        $sheet->setCellValue("B{$r}", 'TOTAL BEBAN OPERASIONAL');
        $sheet->setCellValue("D{$r}", $totalExp);
        $sheet->setCellValue("E{$r}", $netSales > 0 ? ($totalExp / $netSales) : 0);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
        $this->styleSubtotalRow($sheet, "B{$r}:E{$r}");
        $r += 2;

        // 5. LABA BERSIH
        $netProfit = (float) ($data['net_profit'] ?? 0);
        $sheet->setCellValue("B{$r}", 'ESTIMASI LABA BERSIH OPERASIONAL (NET PROFIT)');
        $sheet->setCellValue("D{$r}", $netProfit);
        $sheet->setCellValue("E{$r}", $netSales > 0 ? ($netProfit / $netSales) : 0);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
        $this->styleGrandTotalRow($sheet, "B{$r}:E{$r}", $netProfit >= 0);

        // Auto-size columns
        foreach (['B', 'C', 'D', 'E'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
        $sheet->freezePane('B7');
    }

    // =========================================================================
    // SHEET 3: ARUS KAS (CASH FLOW)
    // =========================================================================
    private function buildCashFlowSheet(Worksheet $sheet, Business $business, array $data): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'LAPORAN ARUS KAS (CASH FLOW STATEMENT)');
        $sheet->setCellValue('B4', 'Periode: ' . ($data['period']['label'] ?? '-'));
        $sheet->getStyle('B2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $sheet->getStyle('B3')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('B4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $r = 6;
        $sheet->setCellValue("B{$r}", 'Aktivitas Arus Kas');
        $sheet->setCellValue("C{$r}", 'Nominal Masuk (Rp)');
        $sheet->setCellValue("D{$r}", 'Nominal Keluar (Rp)');
        $sheet->setCellValue("E{$r}", 'Arus Bersih (Rp)');
        $this->styleTableHeader($sheet, "B{$r}:E{$r}");
        $r++;

        // A. PENERIMAAN KAS
        $sheet->setCellValue("B{$r}", 'A. PENERIMAAN KAS DARI OPERASIONAL');
        $sheet->mergeCells("B{$r}:E{$r}");
        $sheet->getStyle("B{$r}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $r++;

        $cashInItems = [
            ['Penjualan Kasir Tunai POS', (float) ($data['operating_activities']['cash_in']['pos_cash'] ?? 0)],
            ['Penjualan Kasir Non-Tunai (QRIS/Transfer/EDC)', (float) ($data['operating_activities']['cash_in']['pos_non_cash'] ?? 0)],
            ['Pelunasan Pembayaran Faktur Klien', (float) ($data['operating_activities']['cash_in']['invoice_payments'] ?? 0)],
            ['Pemasukan Kas & Tambahan Modal Kasir', (float) ($data['operating_activities']['cash_in']['manual_cash_in'] ?? 0)],
        ];

        foreach ($cashInItems as $item) {
            $sheet->setCellValue("B{$r}", '   ' . $item[0]);
            $sheet->setCellValue("C{$r}", $item[1]);
            $sheet->getStyle("C{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $this->applyBorderThin($sheet, "B{$r}:E{$r}");
            $r++;
        }

        $totalCashIn = (float) ($data['operating_activities']['cash_in']['total'] ?? 0);
        $sheet->setCellValue("B{$r}", 'Subtotal Penerimaan Kas');
        $sheet->setCellValue("C{$r}", $totalCashIn);
        $this->styleSubtotalRow($sheet, "B{$r}:E{$r}");
        $r += 2;

        // B. PENGELUARAN KAS
        $sheet->setCellValue("B{$r}", 'B. PENGELUARAN KAS UNTUK OPERASIONAL');
        $sheet->mergeCells("B{$r}:E{$r}");
        $sheet->getStyle("B{$r}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $r++;

        $cashOutItems = [
            ['Pembayaran Beban Operasional Toko', (float) ($data['operating_activities']['cash_out']['expenses'] ?? 0)],
            ['Pembayaran Tagihan Supplier / Vendor (PO)', (float) ($data['operating_activities']['cash_out']['supplier_payments'] ?? 0)],
            ['Pengeluaran Kas Kecil Kasir', (float) ($data['operating_activities']['cash_out']['manual_cash_out'] ?? 0)],
        ];

        foreach ($cashOutItems as $item) {
            $sheet->setCellValue("B{$r}", '   ' . $item[0]);
            $sheet->setCellValue("D{$r}", $item[1]);
            $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $this->applyBorderThin($sheet, "B{$r}:E{$r}");
            $r++;
        }

        $totalCashOut = (float) ($data['operating_activities']['cash_out']['total'] ?? 0);
        $sheet->setCellValue("B{$r}", 'Subtotal Pengeluaran Kas');
        $sheet->setCellValue("D{$r}", $totalCashOut);
        $this->styleSubtotalRow($sheet, "B{$r}:E{$r}");
        $r += 2;

        // C. ARUS KAS BERSIH
        $netCash = (float) ($data['summary']['net_cash_flow'] ?? 0);
        $sheet->setCellValue("B{$r}", 'ARUS KAS BERSIH OPERASIONAL (NET CASH FLOW)');
        $sheet->setCellValue("E{$r}", $netCash);
        $this->styleGrandTotalRow($sheet, "B{$r}:E{$r}", $netCash >= 0);
        $r += 2;

        // D. REKONSILIASI SALDO KAS & BANK
        $sheet->setCellValue("B{$r}", 'C. REKONSILIASI SALDO KAS & BANK');
        $sheet->mergeCells("B{$r}:E{$r}");
        $sheet->getStyle("B{$r}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $r++;

        $openingBal = (float) ($data['summary']['opening_balance'] ?? 0);
        $closingBal = (float) ($data['summary']['closing_balance'] ?? 0);

        $sheet->setCellValue("B{$r}", '   Saldo Kas Awal Periode');
        $sheet->setCellValue("E{$r}", $openingBal);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $r++;

        $sheet->setCellValue("B{$r}", '   Perubahan Kas Bersih Periode Ini');
        $sheet->setCellValue("E{$r}", $netCash);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $r++;

        $sheet->setCellValue("B{$r}", 'SALDO KAS AKHIR PERIODE (TERSEDIA)');
        $sheet->setCellValue("E{$r}", $closingBal);
        $this->styleTotalRow($sheet, "B{$r}:E{$r}");
        $r += 2;

        // Accounts Breakdown
        $accounts = $data['cash_accounts'] ?? [];
        if (!empty($accounts)) {
            $sheet->setCellValue("B{$r}", 'Rincian Saldo per Rekening / Kasir:');
            $sheet->getStyle("B{$r}")->getFont()->setBold(true);
            $r++;

            foreach ($accounts as $acc) {
                $sheet->setCellValue("B{$r}", '   ' . ($acc['name'] ?? 'Akun'));
                $sheet->setCellValue("E{$r}", (float) ($acc['current_balance'] ?? 0));
                $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
                $this->applyBorderThin($sheet, "B{$r}:E{$r}");
                $r++;
            }
        }

        foreach (['B', 'C', 'D', 'E'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
        $sheet->freezePane('B7');
    }

    // =========================================================================
    // SHEET 4: VALUASI & PERPUTARAN STOK
    // =========================================================================
    private function buildStockValuationSheet(Worksheet $sheet, Business $business, array $data): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'LAPORAN VALUASI PERSEDIAAN & PERPUTARAN STOK (INVENTORY VALUATION & TURNOVER)');
        $sheet->setCellValue('B4', 'Metode Penilaian: Weighted Average Cost (WAC) | Update: ' . now()->translatedFormat('d F Y H:i'));
        $sheet->getStyle('B2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $sheet->getStyle('B3')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('B4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $r = 6;
        $headers = [
            'No',
            'SKU / Kode',
            'Nama Produk / Barang',
            'Kategori',
            'Satuan',
            'Stok Fisik Saat Ini',
            'Modal Rata-rata (WAC / Unit)',
            'Total Nilai Valuasi (Rp)',
            'Penjualan 30 Hari (Unit)',
            'Status Perputaran',
        ];

        $col = 'B';
        foreach ($headers as $h) {
            $sheet->setCellValue("{$col}{$r}", $h);
            $col++;
        }
        $lastCol = chr(ord($col) - 1);
        $this->styleTableHeader($sheet, "B{$r}:{$lastCol}{$r}");
        $r++;

        $items = $data['all_items'] ?? [];
        $startDataRow = $r;

        foreach ($items as $idx => $it) {
            $col = 'B';
            $sheet->setCellValue("{$col}{$r}", $idx + 1);
            $col++;
            $sheet->setCellValue("{$col}{$r}", $it['sku'] ?? '-');
            $col++;
            $sheet->setCellValue("{$col}{$r}", $it['name'] ?? '-');
            $col++;
            $sheet->setCellValue("{$col}{$r}", $it['category'] ?? '-');
            $col++;
            $sheet->setCellValue("{$col}{$r}", $it['unit'] ?? 'pcs');
            $col++;

            // Numbers
            $qty = (float) ($it['current_stock'] ?? 0);
            $sheet->setCellValue("{$col}{$r}", $qty);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $col++;

            $cost = (float) ($it['unit_cost'] ?? 0);
            $sheet->setCellValue("{$col}{$r}", $cost);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $col++;

            $val = (float) ($it['valuation'] ?? ($qty * $cost));
            $sheet->setCellValue("{$col}{$r}", $val);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $col++;

            $sold30 = (float) ($it['sold_30d_qty'] ?? 0);
            $sheet->setCellValue("{$col}{$r}", $sold30);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $vel = $it['velocity'] ?? 'normal';
            $velLabel = match ($vel) {
                'fast_moving' => 'Fast Moving 🔥',
                'slow_moving' => 'Slow Moving ⚠️',
                default => 'Normal',
            };
            $sheet->setCellValue("{$col}{$r}", $velLabel);

            // Zebra styling
            if ($idx % 2 === 1) {
                $sheet->getStyle("B{$r}:{$lastCol}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $this->applyBorderThin($sheet, "B{$r}:{$lastCol}{$r}");
            $r++;
        }

        $endDataRow = max($startDataRow, $r - 1);

        // Grand Total Row
        $sheet->setCellValue("B{$r}", 'TOTAL KESELURUHAN');
        $sheet->mergeCells("B{$r}:F{$r}");
        $sheet->setCellValue("G{$r}", "=SUM(G{$startDataRow}:G{$endDataRow})");
        $sheet->setCellValue("I{$r}", "=SUM(I{$startDataRow}:I{$endDataRow})");
        $sheet->setCellValue("J{$r}", "=SUM(J{$startDataRow}:J{$endDataRow})");

        $sheet->getStyle("G{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("I{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $sheet->getStyle("J{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $this->styleTotalRow($sheet, "B{$r}:{$lastCol}{$r}");

        foreach (range('B', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
        $sheet->freezePane('B7');
        $sheet->setAutoFilter("B6:{$lastCol}{$endDataRow}");
    }

    // =========================================================================
    // SHEET 5: PIUTANG & HUTANG (AR & AP AGING)
    // =========================================================================
    private function buildAgingSheet(Worksheet $sheet, Business $business, array $data): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'ANALISIS UMUR PIUTANG & HUTANG USAHA (AR & AP AGING REPORT)');
        $sheet->setCellValue('B4', 'Kondisi Piutang Klien & Kewajiban Hutang Vendor per: ' . now()->translatedFormat('d F Y H:i'));
        $sheet->getStyle('B2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $sheet->getStyle('B3')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('B4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        // SECTION 1: AGING BUCKETS SUMMARY
        $r = 6;
        $sheet->setCellValue("B{$r}", 'REKAPITULASI BUCKET UMUR TAGIHAN (AGING SUMMARY)');
        $sheet->mergeCells("B{$r}:G{$r}");
        $this->styleSectionHeader($sheet, "B{$r}:G{$r}");
        $r++;

        $sheet->setCellValue("B{$r}", 'Kategori Tagihan');
        $sheet->setCellValue("C{$r}", 'Lancar (0 - 30 Hari)');
        $sheet->setCellValue("D{$r}", '31 - 60 Hari');
        $sheet->setCellValue("E{$r}", '61 - 90 Hari');
        $sheet->setCellValue("F{$r}", '> 90 Hari (Macet)');
        $sheet->setCellValue("G{$r}", 'Total Tagihan (Rp)');
        $this->styleTableHeader($sheet, "B{$r}:G{$r}");
        $r++;

        // AR Summary row
        $ar = $data['ar']['buckets'] ?? [];
        $sheet->setCellValue("B{$r}", 'Piutang Usaha Klien (AR)');
        $sheet->setCellValue("C{$r}", (float) ($ar['0_30'] ?? 0));
        $sheet->setCellValue("D{$r}", (float) ($ar['31_60'] ?? 0));
        $sheet->setCellValue("E{$r}", (float) ($ar['61_90'] ?? 0));
        $sheet->setCellValue("F{$r}", (float) ($ar['over_90'] ?? 0));
        $sheet->setCellValue("G{$r}", (float) ($data['ar']['total_receivables'] ?? 0));
        $this->styleDataRowCurrency($sheet, "C{$r}:G{$r}");
        $this->applyBorderThin($sheet, "B{$r}:G{$r}");
        $r++;

        // AP Summary row
        $ap = $data['ap']['buckets'] ?? [];
        $sheet->setCellValue("B{$r}", 'Hutang Tagihan Supplier (AP)');
        $sheet->setCellValue("C{$r}", (float) ($ap['0_30'] ?? 0));
        $sheet->setCellValue("D{$r}", (float) ($ap['31_60'] ?? 0));
        $sheet->setCellValue("E{$r}", (float) ($ap['61_90'] ?? 0));
        $sheet->setCellValue("F{$r}", (float) ($ap['over_90'] ?? 0));
        $sheet->setCellValue("G{$r}", (float) ($data['ap']['total_payables'] ?? 0));
        $this->styleDataRowCurrency($sheet, "C{$r}:G{$r}");
        $this->applyBorderThin($sheet, "B{$r}:G{$r}");
        $r += 3;

        // SECTION 2: DETAIL PIUTANG (AR SCHEDULE)
        $sheet->setCellValue("B{$r}", 'RINCIAN FAKTUR PIUTANG KLIEN BELUM LUNAS (ACCOUNTS RECEIVABLE)');
        $sheet->mergeCells("B{$r}:I{$r}");
        $this->styleSectionHeader($sheet, "B{$r}:I{$r}");
        $r++;

        $arHeaders = ['No', 'No. Faktur / Invoice', 'Nama Pelanggan', 'Tanggal Faktur', 'Jatuh Tempo', 'Hari Lewat', 'Total Tagihan', 'Sudah Dibayar', 'Sisa Piutang'];
        $col = 'B';
        foreach ($arHeaders as $h) {
            $sheet->setCellValue("{$col}{$r}", $h);
            $col++;
        }
        $lastArCol = chr(ord($col) - 1);
        $this->styleTableHeader($sheet, "B{$r}:{$lastArCol}{$r}");
        $r++;

        $arDetails = $data['ar']['details'] ?? [];
        if (empty($arDetails)) {
            $sheet->setCellValue("B{$r}", 'Tidak ada piutang klien yang tertunda.');
            $sheet->mergeCells("B{$r}:{$lastArCol}{$r}");
            $sheet->getStyle("B{$r}")->getFont()->setItalic(true)->getColor()->setRGB('94A3B8');
            $r++;
        } else {
            foreach ($arDetails as $idx => $d) {
                $sheet->setCellValue("B{$r}", $idx + 1);
                $sheet->setCellValue("C{$r}", $d['invoice_number'] ?? '-');
                $sheet->setCellValue("D{$r}", $d['customer_name'] ?? 'Pelanggan');
                $sheet->setCellValue("E{$r}", $d['invoice_date'] ?? '-');
                $sheet->setCellValue("F{$r}", $d['due_date'] ?? '-');

                $days = (int) ($d['days_overdue'] ?? 0);
                $sheet->setCellValue("G{$r}", $days > 0 ? $days . ' Hari' : 'Lancar');
                if ($days > 30) {
                    $sheet->getStyle("G{$r}")->getFont()->getColor()->setRGB('E11D48')->setBold(true);
                }

                $sheet->setCellValue("H{$r}", (float) ($d['total_amount'] ?? 0));
                $sheet->setCellValue("I{$r}", (float) ($d['paid_amount'] ?? 0));
                $sheet->setCellValue("J{$r}", (float) ($d['balance_due'] ?? 0));

                $sheet->getStyle("H{$r}:J{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
                if ($idx % 2 === 1) {
                    $sheet->getStyle("B{$r}:J{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
                }
                $this->applyBorderThin($sheet, "B{$r}:J{$r}");
                $r++;
            }
        }

        $r += 2;

        // SECTION 3: DETAIL HUTANG (AP SCHEDULE)
        $sheet->setCellValue("B{$r}", 'RINCIAN TAGIHAN HUTANG SUPPLIER BELUM LUNAS (ACCOUNTS PAYABLE)');
        $sheet->mergeCells("B{$r}:I{$r}");
        $this->styleSectionHeader($sheet, "B{$r}:I{$r}");
        $r++;

        $apHeaders = ['No', 'No. Tagihan Vendor', 'Supplier / Vendor', 'Tanggal Tagihan', 'Jatuh Tempo', 'Hari Lewat', 'Total Tagihan', 'Sudah Dibayar', 'Sisa Hutang'];
        $col = 'B';
        foreach ($apHeaders as $h) {
            $sheet->setCellValue("{$col}{$r}", $h);
            $col++;
        }
        $lastApCol = chr(ord($col) - 1);
        $this->styleTableHeader($sheet, "B{$r}:{$lastApCol}{$r}");
        $r++;

        $apDetails = $data['ap']['details'] ?? [];
        if (empty($apDetails)) {
            $sheet->setCellValue("B{$r}", 'Tidak ada hutang tagihan supplier yang belum lunas.');
            $sheet->mergeCells("B{$r}:{$lastApCol}{$r}");
            $sheet->getStyle("B{$r}")->getFont()->setItalic(true)->getColor()->setRGB('94A3B8');
            $r++;
        } else {
            foreach ($apDetails as $idx => $d) {
                $sheet->setCellValue("B{$r}", $idx + 1);
                $sheet->setCellValue("C{$r}", $d['invoice_number'] ?? '-');
                $sheet->setCellValue("D{$r}", $d['supplier_name'] ?? 'Vendor');
                $sheet->setCellValue("E{$r}", $d['invoice_date'] ?? '-');
                $sheet->setCellValue("F{$r}", $d['due_date'] ?? '-');

                $days = (int) ($d['days_overdue'] ?? 0);
                $sheet->setCellValue("G{$r}", $days > 0 ? $days . ' Hari' : 'Belum Jatuh Tempo');
                if ($days > 30) {
                    $sheet->getStyle("G{$r}")->getFont()->getColor()->setRGB('EA580C')->setBold(true);
                }

                $sheet->setCellValue("H{$r}", (float) ($d['total_amount'] ?? 0));
                $sheet->setCellValue("I{$r}", (float) ($d['paid_amount'] ?? 0));
                $sheet->setCellValue("J{$r}", (float) ($d['balance_due'] ?? 0));

                $sheet->getStyle("H{$r}:J{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
                if ($idx % 2 === 1) {
                    $sheet->getStyle("B{$r}:J{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
                }
                $this->applyBorderThin($sheet, "B{$r}:J{$r}");
                $r++;
            }
        }

        foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'] as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
    }

    // =========================================================================
    // SHEET 6: HPP & MARGIN PRODUK (RECIPE / BOM COSTING)
    // =========================================================================
    private function buildHppSheet(Worksheet $sheet, Business $business, array $hppReport, array $costBreakdown): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'ANALISIS HARGA POKOK PRODUKSI (HPP) & MARGIN PER PRODUK');
        $sheet->setCellValue('B4', 'Rincian Elemen Biaya Bahan, Tenaga Kerja, Mesin & Overhead per Unit Produk');
        $sheet->getStyle('B2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_ACCENT_BG);
        $sheet->getStyle('B3')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('B4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $r = 6;
        $headers = [
            'No',
            'SKU',
            'Nama Produk',
            'Kategori',
            'Satuan',
            'Biaya Bahan (Rp)',
            'Biaya Labor/Upah (Rp)',
            'Biaya Mesin (Rp)',
            'Biaya Overhead (Rp)',
            'Total HPP / Unit (Rp)',
            'Harga Jual Rekomendasi (Rp)',
            'Nominal Margin (Rp)',
            'Margin (%)',
            'Markup (%)',
        ];

        $col = 'B';
        foreach ($headers as $h) {
            $sheet->setCellValue("{$col}{$r}", $h);
            $col++;
        }
        $lastCol = chr(ord($col) - 1);
        $this->styleTableHeader($sheet, "B{$r}:{$lastCol}{$r}");
        $r++;

        $startDataRow = $r;
        foreach ($hppReport as $idx => $p) {
            $col = 'B';
            $sheet->setCellValue("{$col}{$r}", $idx + 1);
            $col++;
            $sheet->setCellValue("{$col}{$r}", $p['sku'] ?? '-');
            $col++;
            $sheet->setCellValue("{$col}{$r}", $p['product_name'] ?? '-');
            $col++;
            $sheet->setCellValue("{$col}{$r}", $p['category'] ?? '-');
            $col++;
            $sheet->setCellValue("{$col}{$r}", $p['output_unit'] ?? 'pcs');
            $col++;

            // Costs
            $mat = (float) ($p['material_cost'] ?? 0);
            $sheet->setCellValue("{$col}{$r}", $mat);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $col++;

            $lab = (float) ($p['labor_cost'] ?? 0);
            $sheet->setCellValue("{$col}{$r}", $lab);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $col++;

            $mac = (float) ($p['machine_cost'] ?? 0);
            $sheet->setCellValue("{$col}{$r}", $mac);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $col++;

            $ovh = (float) ($p['overhead_cost'] ?? 0);
            $sheet->setCellValue("{$col}{$r}", $ovh);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $col++;

            // Total HPP
            $hpp = (float) ($p['hpp_per_unit'] ?? 0);
            $sheet->setCellValue("{$col}{$r}", $hpp);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $sheet->getStyle("{$col}{$r}")->getFont()->setBold(true);
            $col++;

            // Selling Price
            $rec = (float) ($p['recommended_price'] ?? 0);
            $sheet->setCellValue("{$col}{$r}", $rec);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $col++;

            // Profit
            $profit = (float) ($p['gross_profit'] ?? 0);
            $sheet->setCellValue("{$col}{$r}", $profit);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $col++;

            // Margin %
            $marginPct = ((float) ($p['margin_percentage'] ?? 0)) / 100;
            $sheet->setCellValue("{$col}{$r}", $marginPct);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('0.0%');
            $col++;

            // Markup %
            $markupPct = ((float) ($p['markup_percentage'] ?? 0)) / 100;
            $sheet->setCellValue("{$col}{$r}", $markupPct);
            $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode('0.0%');

            if ($idx % 2 === 1) {
                $sheet->getStyle("B{$r}:{$lastCol}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $this->applyBorderThin($sheet, "B{$r}:{$lastCol}{$r}");
            $r++;
        }

        $endDataRow = max($startDataRow, $r - 1);

        foreach (range('B', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
        $sheet->freezePane('B7');
        $sheet->setAutoFilter("B6:{$lastCol}{$endDataRow}");
    }

    // =========================================================================
    // HELPER STYLING UTILITIES
    // =========================================================================
    private function renderKpiCard(
        Worksheet $sheet,
        string $colStart,
        string $colEnd,
        int $rowStart,
        string $title,
        float $value,
        string $subtitle,
        string $bgColor,
        string $accentColor,
        string $numFormat
    ): void {
        $r1 = $rowStart;
        $r2 = $rowStart + 1;
        $r3 = $rowStart + 2;

        // Title row
        $sheet->setCellValue("{$colStart}{$r1}", $title);
        $sheet->mergeCells("{$colStart}{$r1}:{$colEnd}{$r1}");
        $sheet->getStyle("{$colStart}{$r1}")->getFont()->setSize(8)->setBold(true)->getColor()->setRGB('475569');

        // Value row
        $sheet->setCellValue("{$colStart}{$r2}", $value);
        $sheet->mergeCells("{$colStart}{$r2}:{$colEnd}{$r2}");
        $sheet->getStyle("{$colStart}{$r2}")->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_HEADER_BG);
        $sheet->getStyle("{$colStart}{$r2}")->getNumberFormat()->setFormatCode($numFormat);

        // Subtitle row
        $sheet->setCellValue("{$colStart}{$r3}", $subtitle);
        $sheet->mergeCells("{$colStart}{$r3}:{$colEnd}{$r3}");
        $sheet->getStyle("{$colStart}{$r3}")->getFont()->setSize(8)->setItalic(true)->getColor()->setRGB('64748B');

        // Card Container Styling
        $range = "{$colStart}{$r1}:{$colEnd}{$r3}";
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgColor);
        $sheet->getStyle($range)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB($accentColor);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function renderReportRow(
        Worksheet $sheet,
        int $row,
        string $label,
        ?float $subtotal,
        ?float $total,
        float $netSales,
        bool $isBold = false
    ): void {
        $sheet->setCellValue("B{$row}", $label);
        if ($subtotal !== null) {
            $sheet->setCellValue("C{$row}", $subtotal);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('Rp #,##0');
        }
        if ($total !== null) {
            $sheet->setCellValue("D{$row}", $total);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('Rp #,##0');

            if ($netSales > 0) {
                $pct = $total / $netSales;
                $sheet->setCellValue("E{$row}", $pct);
                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            }
        }

        if ($isBold) {
            $sheet->getStyle("B{$row}:E{$row}")->getFont()->setBold(true);
        }
        $this->applyBorderThin($sheet, "B{$row}:E{$row}");
    }

    private function styleTableHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(10)->getColor()->setRGB(self::COLOR_HEADER_FG);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_HEADER_BG);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('334155');
    }

    private function styleSectionHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(10)->getColor()->setRGB(self::COLOR_HEADER_FG);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ACCENT_BG);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function styleSubtotalRow(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($range)->getNumberFormat()->setFormatCode('Rp #,##0');
    }

    private function styleTotalRow(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB(self::COLOR_HEADER_BG);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_TOTAL_BG);
        $sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $sheet->getStyle($range)->getNumberFormat()->setFormatCode('Rp #,##0');
    }

    private function styleGrandTotalRow(Worksheet $sheet, string $range, bool $isPositive = true): void
    {
        $bgColor = $isPositive ? 'D1FAE5' : 'FFE4E6'; // Green 100 or Rose 100
        $fgColor = $isPositive ? '065F46' : '9F1239'; // Green 800 or Rose 800

        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB($fgColor);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgColor);
        $sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB($fgColor);
        $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->getColor()->setRGB($fgColor);
        $sheet->getStyle($range)->getNumberFormat()->setFormatCode('Rp #,##0');
    }

    private function styleDataRowCurrency(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getNumberFormat()->setFormatCode('Rp #,##0');
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    private function applyBorderThin(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_HAIR)->getColor()->setRGB('CBD5E1');
    }
}
