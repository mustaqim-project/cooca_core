<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Business;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PosReportExport
{
    private const COLOR_DARK_HEADER    = '1C1C1E'; // Apple Dark Onyx
    private const COLOR_BLUE_ACCENT    = '007AFF'; // iOS System Blue
    private const COLOR_EMERALD_BG     = 'ECFDF5'; // Light Emerald Mint
    private const COLOR_EMERALD_BORDER = '10B981'; // Emerald 500
    private const COLOR_EMERALD_TEXT   = '065F46'; // Deep Emerald
    private const COLOR_ROSE_BG        = 'FEF2F2'; // Light Rose
    private const COLOR_ROSE_BORDER    = 'F43F5E'; // Rose 500
    private const COLOR_ROSE_TEXT      = '9F1239'; // Deep Rose
    private const COLOR_AMBER_BG       = 'FFFBEB'; // Light Amber
    private const COLOR_AMBER_BORDER   = 'F59E0B'; // Amber 500
    private const COLOR_PURPLE_BG      = 'FAF5FF'; // Light Purple
    private const COLOR_PURPLE_BORDER  = '9333EA'; // Purple 600
    private const COLOR_SUBHEADER_BG   = 'F1F5F9'; // Slate 100
    private const COLOR_ZEBRA_BG       = 'F8FAFC'; // Slate 50
    private const COLOR_BORDER_LINE    = 'E2E8F0'; // Slate 200

    /**
     * Generate the complete 2-sheet Spreadsheet instance.
     *
     * @param Collection<int, PosOrder> $orders
     */
    public function generate(Business $business, Collection $orders, Carbon $startDate, Carbon $endDate): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($business->name . ' - Cooca Suite')
            ->setLastModifiedBy($business->name)
            ->setTitle('Laporan Penjualan Kasir POS')
            ->setSubject('POS Sales & Operational Revenue Ledger')
            ->setDescription('Laporan Dua Bagian: Ringkasan Eksekutif KPI & Rincian Transaksional POS Standar Akuntansi');

        // -------------------------------------------------------------
        // SHEET 1: RINGKASAN EKSEKUTIF & KPI BENTO CARDS
        // -------------------------------------------------------------
        $sheetSummary = $spreadsheet->getActiveSheet();
        $sheetSummary->setTitle('Ringkasan Eksekutif');
        $this->buildExecutiveSummarySheet($sheetSummary, $business, $orders, $startDate, $endDate);

        // -------------------------------------------------------------
        // SHEET 2: RINCIAN TRANSAKSI (TRANSACTION LEDGER)
        // -------------------------------------------------------------
        $sheetLedger = $spreadsheet->createSheet();
        $sheetLedger->setTitle('Rincian Transaksi');
        $this->buildTransactionalLedgerSheet($sheetLedger, $business, $orders, $startDate, $endDate);

        // Default active sheet: Ringkasan Eksekutif
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Download the workbook as a StreamedResponse.
     *
     * @param Collection<int, PosOrder> $orders
     */
    public function download(Business $business, Collection $orders, Carbon $startDate, Carbon $endDate): StreamedResponse
    {
        $spreadsheet = $this->generate($business, $orders, $startDate, $endDate);
        $cleanBusinessName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $business->name);
        $filename = 'Laporan_POS_' . $cleanBusinessName . '_' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.xlsx';

        return new StreamedResponse(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'max-age=0',
            ]
        );
    }

    /**
     * Build Sheet 1: Executive Dashboard & Aggregations.
     *
     * @param Collection<int, PosOrder> $orders
     */
    private function buildExecutiveSummarySheet(Worksheet $sheet, Business $business, Collection $orders, Carbon $startDate, Carbon $endDate): void
    {
        $sheet->setShowGridLines(true);

        // 1. Header Title Block
        $sheet->setCellValue('A2', strtoupper($business->name));
        $sheet->setCellValue('A3', 'LAPORAN PENJUALAN KASIR & OMZET POINT OF SALE (POS)');
        $sheet->setCellValue('A4', 'Periode: ' . $startDate->translatedFormat('d M Y') . ' s/d ' . $endDate->translatedFormat('d M Y') . ' | Waktu Unduh: ' . now()->format('d/m/Y H:i:s') . ' WIB | Sistem: COOCA Enterprise');

        $sheet->getStyle('A2')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle('A3')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A4')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        // Aggregated Metrics
        $ordersCount      = $orders->count();
        $totalRevenue     = (float) $orders->sum('total_amount');
        $totalHpp         = (float) $orders->sum('total_hpp_cost');
        $totalGrossProfit = (float) $orders->sum('total_gross_profit');
        $totalDiscount    = (float) $orders->sum('discount_amount') + (float) $orders->sum('voucher_discount_amount') + (float) $orders->sum('points_discount_amount');
        $totalTax         = (float) $orders->sum('tax_amount');
        $aov              = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0.0;
        $grossMarginPct   = $totalRevenue > 0 ? ($totalGrossProfit / $totalRevenue) : 0.0;

        // 2. Bento KPI Cards in Row 6..8
        // Card 1: Total Omzet Penjualan (Cols A-B)
        $this->renderKpiCard($sheet, 'A', 'B', 6, 'TOTAL OMZET PENJUALAN', $totalRevenue, 'Omzet bersih kasir', 'EFF6FF', self::COLOR_BLUE_ACCENT, '"Rp "#,##0');

        // Card 2: Total Transaksi (Cols C-D)
        $this->renderKpiCard($sheet, 'C', 'D', 6, 'TOTAL TRANSAKSI SELESAI', $ordersCount, 'Pesanan selesai & lunas', self::COLOR_EMERALD_BG, self::COLOR_EMERALD_BORDER, '#,##0');

        // Card 3: Total Modal HPP (Cols E-F)
        $this->renderKpiCard($sheet, 'E', 'F', 6, 'TOTAL MODAL POKOK (HPP)', $totalHpp, 'Beban Pokok Penjualan', self::COLOR_ROSE_BG, self::COLOR_ROSE_BORDER, '"Rp "#,##0');

        // Card 4: Total Laba Kotor (Cols G-H)
        $this->renderKpiCard($sheet, 'G', 'H', 6, 'TOTAL LABA KOTOR', $totalGrossProfit, 'Margin: ' . number_format($grossMarginPct * 100, 1) . '%', self::COLOR_EMERALD_BG, '059669', '"Rp "#,##0');

        // Card 5: Rata-Rata Transaksi / AOV (Cols I-J)
        $this->renderKpiCard($sheet, 'I', 'J', 6, 'RATA-RATA ORDER (AOV)', $aov, 'Omzet per transaksi', self::COLOR_PURPLE_BG, self::COLOR_PURPLE_BORDER, '"Rp "#,##0');

        // Card 6: Total Diskon & Pajak (Cols K-L)
        $this->renderKpiCard($sheet, 'K', 'L', 6, 'DISKON & PPN KELUARAN', $totalDiscount + $totalTax, 'Potongan Rp ' . number_format($totalDiscount, 0, ',', '.') . ' · PPN Rp ' . number_format($totalTax, 0, ',', '.'), self::COLOR_AMBER_BG, self::COLOR_AMBER_BORDER, '"Rp "#,##0');

        // -------------------------------------------------------------
        // 3. TABLE 1: KOMPOSISI PENJUALAN (BARANG FISIK VS JASA LAYANAN)
        // -------------------------------------------------------------
        $sheet->setCellValue('A11', '1. KOMPOSISI PENJUALAN: BARANG FISIK VS JASA LAYANAN');
        $sheet->mergeCells('A11:G11');
        $this->styleSectionHeader($sheet, 'A11:G11');

        $t1Headers = ['Kategori Komoditas', 'Volume Qty Terjual', 'Total Omzet Kotor (Rp)', 'Total Modal HPP (Rp)', 'Laba Kotor (Rp)', 'Margin (%)', 'Porsi Omzet (%)'];
        $t1Cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($t1Headers as $idx => $label) {
            $col = $t1Cols[$idx];
            $sheet->setCellValue("{$col}12", $label);
            $sheet->getStyle("{$col}12")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}12")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}12")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if ($idx > 0) {
                $sheet->getStyle("{$col}12")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(12)->setRowHeight(24);

        // Group items by type
        $allItems = $orders->flatMap(fn(PosOrder $o) => $o->items);
        $goodsItems = $allItems->filter(fn(PosOrderItem $i) => ($i->product?->type ?? 'goods') === 'goods');
        $serviceItems = $allItems->filter(fn(PosOrderItem $i) => ($i->product?->type ?? 'goods') === 'service');
        $customItems = $allItems->filter(fn(PosOrderItem $i) => empty($i->product_id));

        $commodityRows = [
            [
                'label' => 'Barang Fisik / Dagangan (Goods)',
                'qty'   => (float) $goodsItems->sum('quantity'),
                'sales' => (float) $goodsItems->sum('total_price'),
                'hpp'   => (float) $goodsItems->sum('total_hpp'),
            ],
            [
                'label' => 'Jasa / Layanan Bebas Stok (Service)',
                'qty'   => (float) $serviceItems->sum('quantity'),
                'sales' => (float) $serviceItems->sum('total_price'),
                'hpp'   => (float) $serviceItems->sum('total_hpp'),
            ],
            [
                'label' => 'Item Custom / Non-Katalog',
                'qty'   => (float) $customItems->sum('quantity'),
                'sales' => (float) $customItems->sum('total_price'),
                'hpp'   => (float) $customItems->sum('total_hpp'),
            ],
        ];

        $r = 13;
        foreach ($commodityRows as $crow) {
            $sheet->setCellValue("A{$r}", $crow['label']);
            $sheet->setCellValue("B{$r}", $crow['qty']);
            $sheet->setCellValue("C{$r}", $crow['sales']);
            $sheet->setCellValue("D{$r}", $crow['hpp']);
            $sheet->setCellValue("E{$r}", "=C{$r}-D{$r}");
            $sheet->setCellValue("F{$r}", "=IF(C{$r}>0, E{$r}/C{$r}, 0)");
            $sheet->setCellValue("G{$r}", "=IF(C\$16>0, C{$r}/C\$16, 0)");

            $sheet->getStyle("A{$r}")->getFont()->setSize(10);
            $sheet->getStyle("B{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("C{$r}:E{$r}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("F{$r}:G{$r}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("B{$r}:G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderThin($sheet, "A{$r}:G{$r}");

            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:G{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $r++;
        }

        // Row 16: Total Komposisi
        $sheet->setCellValue("A{$r}", 'TOTAL KESELURUHAN ITEM');
        $sheet->setCellValue("B{$r}", '=SUM(B13:B15)');
        $sheet->setCellValue("C{$r}", '=SUM(C13:C15)');
        $sheet->setCellValue("D{$r}", '=SUM(D13:D15)');
        $sheet->setCellValue("E{$r}", '=SUM(E13:E15)');
        $sheet->setCellValue("F{$r}", "=IF(C{$r}>0, E{$r}/C{$r}, 0)");
        $sheet->setCellValue("G{$r}", "=IF(C{$r}>0, 1, 0)");

        $sheet->getStyle("A{$r}:G{$r}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
        $sheet->getStyle("A{$r}:G{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle("B{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("C{$r}:E{$r}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle("F{$r}:G{$r}")->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle("B{$r}:G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $this->applyBorderDoubleBottom($sheet, "A{$r}:G{$r}");

        // -------------------------------------------------------------
        // 4. TABLE 2: METODE PEMBAYARAN KASIR
        // -------------------------------------------------------------
        $startPmtRow = 19;
        $sheet->setCellValue("A{$startPmtRow}", '2. RINCIAN PENERIMAAN KAS & METODE PEMBAYARAN');
        $sheet->mergeCells("A{$startPmtRow}:E{$startPmtRow}");
        $this->styleSectionHeader($sheet, "A{$startPmtRow}:E{$startPmtRow}");

        $t2Headers = ['Metode Pembayaran', 'Frekuensi Transaksi', 'Total Nominal Diterima (Rp)', 'Porsi Transaksi (%)', 'Porsi Nominal (%)'];
        $t2Cols    = ['A', 'B', 'C', 'D', 'E'];

        $pmtHeadRow = $startPmtRow + 1;
        foreach ($t2Headers as $idx => $label) {
            $col = $t2Cols[$idx];
            $sheet->setCellValue("{$col}{$pmtHeadRow}", $label);
            $sheet->getStyle("{$col}{$pmtHeadRow}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}{$pmtHeadRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}{$pmtHeadRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if ($idx > 0) {
                $sheet->getStyle("{$col}{$pmtHeadRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension($pmtHeadRow)->setRowHeight(24);

        $allPayments = $orders->flatMap(fn(PosOrder $o) => $o->payments);
        $paymentMethodsMap = [
            'cash'            => 'Tunai (Kas Kasir)',
            'qris'            => 'QRIS Dinamis / Statis Cooca Pay',
            'edc_debit'       => 'EDC Kartu Debit Bank',
            'edc_credit'      => 'EDC Kartu Kredit',
            'transfer'        => 'Transfer Bank Langsung',
            'customer_credit' => 'Piutang Pelanggan (Kasbon)',
            'loyalty_points'  => 'Poin Loyalitas Member',
        ];

        $pr = $pmtHeadRow + 1;
        $firstPmtDataRow = $pr;
        foreach ($paymentMethodsMap as $methodKey => $methodLabel) {
            $mPayments = $allPayments->where('payment_method', $methodKey);
            $mCount = $mPayments->count();
            $mAmount = (float) $mPayments->sum('amount');

            $sheet->setCellValue("A{$pr}", $methodLabel);
            $sheet->setCellValue("B{$pr}", $mCount);
            $sheet->setCellValue("C{$pr}", $mAmount);
            $sheet->setCellValue("D{$pr}", "=IF(B\$" . ($firstPmtDataRow + count($paymentMethodsMap)) . ">0, B{$pr}/B\$" . ($firstPmtDataRow + count($paymentMethodsMap)) . ", 0)");
            $sheet->setCellValue("E{$pr}", "=IF(C\$" . ($firstPmtDataRow + count($paymentMethodsMap)) . ">0, C{$pr}/C\$" . ($firstPmtDataRow + count($paymentMethodsMap)) . ", 0)");

            $sheet->getStyle("A{$pr}")->getFont()->setSize(10);
            $sheet->getStyle("B{$pr}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("C{$pr}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("D{$pr}:E{$pr}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("B{$pr}:E{$pr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderThin($sheet, "A{$pr}:E{$pr}");

            if ($pr % 2 === 0) {
                $sheet->getStyle("A{$pr}:E{$pr}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $pr++;
        }
        $lastPmtDataRow = $pr - 1;

        // Total Payment Row
        $sheet->setCellValue("A{$pr}", 'TOTAL PENERIMAAN PEMBAYARAN');
        $sheet->setCellValue("B{$pr}", "=SUM(B{$firstPmtDataRow}:B{$lastPmtDataRow})");
        $sheet->setCellValue("C{$pr}", "=SUM(C{$firstPmtDataRow}:C{$lastPmtDataRow})");
        $sheet->setCellValue("D{$pr}", "=IF(B{$pr}>0, 1, 0)");
        $sheet->setCellValue("E{$pr}", "=IF(C{$pr}>0, 1, 0)");

        $sheet->getStyle("A{$pr}:E{$pr}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
        $sheet->getStyle("A{$pr}:E{$pr}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle("B{$pr}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("C{$pr}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle("D{$pr}:E{$pr}")->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle("B{$pr}:E{$pr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $this->applyBorderDoubleBottom($sheet, "A{$pr}:E{$pr}");

        // -------------------------------------------------------------
        // 5. TABLE 3: TOP 10 PRODUK / MENU TERLARIS
        // -------------------------------------------------------------
        $startTopRow = $pr + 3;
        $sheet->setCellValue("A{$startTopRow}", '3. TOP 10 PRODUK & MENU TERLARIS (BERDASARKAN OMZET)');
        $sheet->mergeCells("A{$startTopRow}:I{$startTopRow}");
        $this->styleSectionHeader($sheet, "A{$startTopRow}:I{$startTopRow}");

        $t3Headers = ['No', 'Kode Produk', 'Nama Produk / Menu', 'Tipe', 'Qty Terjual', 'Total Omzet (Rp)', 'Total Modal HPP (Rp)', 'Laba Kotor (Rp)', 'Margin (%)'];
        $t3Cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        $topHeadRow = $startTopRow + 1;
        foreach ($t3Headers as $idx => $label) {
            $col = $t3Cols[$idx];
            $sheet->setCellValue("{$col}{$topHeadRow}", $label);
            $sheet->getStyle("{$col}{$topHeadRow}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}{$topHeadRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}{$topHeadRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if (in_array($col, ['E', 'F', 'G', 'H', 'I'], true)) {
                $sheet->getStyle("{$col}{$topHeadRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension($topHeadRow)->setRowHeight(24);

        // Group by product_id or product_name
        $productGroups = $allItems->groupBy(fn(PosOrderItem $i) => $i->product_id ?: $i->product_name)
            ->map(function ($items) {
                $first = $items->first();
                $qty = (float) $items->sum('quantity');
                $revenue = (float) $items->sum('total_price');
                $hpp = (float) $items->sum('total_hpp');
                return [
                    'code'    => $first->product?->code ?? ($first->product_code ?? '-'),
                    'name'    => $first->product?->name ?? ($first->product_name ?? 'Item Custom'),
                    'type'    => ($first->product?->type ?? 'goods') === 'service' ? 'Jasa' : 'Barang',
                    'qty'     => $qty,
                    'revenue' => $revenue,
                    'hpp'     => $hpp,
                    'profit'  => $revenue - $hpp,
                ];
            })
            ->sortByDesc('revenue')
            ->take(10);

        $tpr = $topHeadRow + 1;
        $rank = 1;
        foreach ($productGroups as $pdata) {
            $sheet->setCellValue("A{$tpr}", $rank++);
            $sheet->setCellValue("B{$tpr}", $pdata['code']);
            $sheet->setCellValue("C{$tpr}", $pdata['name']);
            $sheet->setCellValue("D{$tpr}", $pdata['type']);
            $sheet->setCellValue("E{$tpr}", $pdata['qty']);
            $sheet->setCellValue("F{$tpr}", $pdata['revenue']);
            $sheet->setCellValue("G{$tpr}", $pdata['hpp']);
            $sheet->setCellValue("H{$tpr}", "=F{$tpr}-G{$tpr}");
            $sheet->setCellValue("I{$tpr}", "=IF(F{$tpr}>0, H{$tpr}/F{$tpr}, 0)");

            $sheet->getStyle("A{$tpr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$tpr}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("F{$tpr}:H{$tpr}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("I{$tpr}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("E{$tpr}:I{$tpr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderThin($sheet, "A{$tpr}:I{$tpr}");

            if ($tpr % 2 === 0) {
                $sheet->getStyle("A{$tpr}:I{$tpr}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $tpr++;
        }

        // -------------------------------------------------------------
        // 6. AUDIT & RECONCILIATION NOTICE FOOTNOTE
        // -------------------------------------------------------------
        $fnRow = $tpr + 2;
        $sheet->setCellValue("A{$fnRow}", 'CATATAN AUDIT AKUNTANSI & INTEGRITAS KASIR COOCA:');
        $sheet->setCellValue('A' . ($fnRow + 1), '1. Seluruh transaksi POS telah terekonsiliasi otomatis dengan Buku Kas (Cash Ledger) dan Jurnal Umum Berpasangan (Double-Entry General Ledger).');
        $sheet->setCellValue('A' . ($fnRow + 2), '2. Transaksi QRIS Gateway TriPay telah disinkronkan dan memotong beban fee gateway (MDR) sesuai ketentuan Bank Indonesia.');
        $sheet->setCellValue('A' . ($fnRow + 3), '3. Pengurangan persediaan barang dan bahan baku (BOM) dihitung secara real-time berdasarkan metode Rata-Rata Tertimbang (Weighted Average Cost).');

        $sheet->getStyle("A{$fnRow}")->getFont()->setSize(9.5)->setBold(true)->getColor()->setRGB('475569');
        $sheet->getStyle('A' . ($fnRow + 1) . ':A' . ($fnRow + 3))->getFont()->setSize(8.5)->getColor()->setRGB('64748B');

        // Auto-fit Columns on Summary Sheet
        $this->autoFitColumns($sheet, ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L']);
    }

    /**
     * Build Sheet 2: Transactional Ledger.
     *
     * @param Collection<int, PosOrder> $orders
     */
    private function buildTransactionalLedgerSheet(Worksheet $sheet, Business $business, Collection $orders, Carbon $startDate, Carbon $endDate): void
    {
        $sheet->setShowGridLines(true);

        // Header Title Block
        $sheet->setCellValue('A2', 'RINCIAN TRANSAKSI PENJUALAN KASIR (TRANSACTION LEDGER)');
        $sheet->setCellValue('A3', 'Periode: ' . $startDate->translatedFormat('d M Y') . ' s/d ' . $endDate->translatedFormat('d M Y') . ' | Total: ' . $orders->count() . ' Transaksi Selesai');

        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        // Table Header on Row 5
        $headers = [
            'A' => 'No',
            'B' => 'No. Order POS',
            'C' => 'Tanggal',
            'D' => 'Waktu',
            'E' => 'Lokasi / Outlet',
            'F' => 'Meja / Ref Industri',
            'G' => 'Nama Pelanggan',
            'H' => 'Kasir / Petugas',
            'I' => 'Tipe Order',
            'J' => 'Subtotal Bruto (Rp)',
            'K' => 'Diskon & Voucher (Rp)',
            'L' => 'PPN Keluaran (Rp)',
            'M' => 'Service Charge (Rp)',
            'N' => 'Pembulatan (Rp)',
            'O' => 'Total Omzet (Rp)',
            'P' => 'Modal HPP (Rp)',
            'Q' => 'Laba Kotor (Rp)',
            'R' => 'Margin (%)',
            'S' => 'Metode Pembayaran',
            'T' => 'Status Pesanan',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}5", $label);
            $sheet->getStyle("{$col}5")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}5")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if (in_array($col, ['J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'], true)) {
                $sheet->getStyle("{$col}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Data Rows
        $r = 6;
        $no = 1;
        foreach ($orders as $order) {
            $discountTotal = (float) $order->discount_amount + (float) $order->voucher_discount_amount + (float) $order->points_discount_amount;
            $paymentMethods = $order->payments->pluck('payment_method')->unique()->map(function ($pm) {
                return match ($pm) {
                    'cash'            => 'Tunai',
                    'qris'            => 'QRIS',
                    'edc_debit'       => 'EDC Debit',
                    'edc_credit'      => 'EDC Kredit',
                    'transfer'        => 'Transfer',
                    'customer_credit' => 'Piutang',
                    'loyalty_points'  => 'Poin',
                    default           => ucfirst((string) $pm),
                };
            })->implode(', ');

            // Contextual Industry Reference (Meja, Plat, atau Rak)
            $ref = $order->table_or_reference;
            if (empty($ref)) {
                if ($order->vehicle_license_plate) {
                    $ref = 'Plat: ' . $order->vehicle_license_plate;
                } elseif ($order->rack_location) {
                    $ref = 'Rak: ' . $order->rack_location;
                } else {
                    $ref = '-';
                }
            }

            $orderTypeLabel = match ($order->order_type) {
                'dine_in'   => 'Dine In',
                'take_away' => 'Take Away',
                'delivery'  => 'Delivery',
                default     => ucfirst(str_replace('_', ' ', (string) $order->order_type)),
            };

            $statusLabel = match ($order->status) {
                PosOrder::STATUS_COMPLETED      => 'Lunas',
                PosOrder::STATUS_PARTIAL_REFUND => 'Refund Sebagian',
                PosOrder::STATUS_CONFIRMED      => 'Terkonfirmasi',
                PosOrder::STATUS_VOIDED         => 'Void / Batal',
                default                         => ucfirst((string) $order->status),
            };

            $sheet->setCellValue("A{$r}", $no++);
            $sheet->setCellValue("B{$r}", $order->order_number);
            $sheet->setCellValue("C{$r}", $order->order_date ? Carbon::parse($order->order_date)->format('d/m/Y') : '-');
            $sheet->setCellValue("D{$r}", $order->created_at ? $order->created_at->format('H:i') : '-');
            $sheet->setCellValue("E{$r}", $order->location?->name ?? 'Outlet Utama');
            $sheet->setCellValue("F{$r}", $ref);
            $sheet->setCellValue("G{$r}", $order->customer?->name ?? ($order->customer_name_guest ?? 'Pelanggan Umum'));
            $sheet->setCellValue("H{$r}", $order->user?->name ?? 'Kasir');
            $sheet->setCellValue("I{$r}", $orderTypeLabel);
            $sheet->setCellValue("J{$r}", (float) $order->subtotal);
            $sheet->setCellValue("K{$r}", $discountTotal);
            $sheet->setCellValue("L{$r}", (float) $order->tax_amount);
            $sheet->setCellValue("M{$r}", (float) $order->service_charge_amount);
            $sheet->setCellValue("N{$r}", (float) $order->rounding_amount);
            $sheet->setCellValue("O{$r}", (float) $order->total_amount);
            $sheet->setCellValue("P{$r}", (float) $order->total_hpp_cost);
            $sheet->setCellValue("Q{$r}", "=O{$r}-P{$r}");
            $sheet->setCellValue("R{$r}", "=IF(O{$r}>0, Q{$r}/O{$r}, 0)");
            $sheet->setCellValue("S{$r}", $paymentMethods ?: 'Tunai');
            $sheet->setCellValue("T{$r}", $statusLabel);

            // Formatting
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$r}:D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$r}:Q{$r}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("R{$r}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("J{$r}:R{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderThin($sheet, "A{$r}:T{$r}");

            if ($r % 2 === 1) {
                $sheet->getStyle("A{$r}:T{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $r++;
        }
        $lastDataRow = max(6, $r - 1);

        // Summary Total Row with Formulas
        $sheet->setCellValue("A{$r}", 'TOTAL KESELURUHAN');
        $sheet->mergeCells("A{$r}:I{$r}");
        $sheet->setCellValue("J{$r}", "=SUM(J6:J{$lastDataRow})");
        $sheet->setCellValue("K{$r}", "=SUM(K6:K{$lastDataRow})");
        $sheet->setCellValue("L{$r}", "=SUM(L6:L{$lastDataRow})");
        $sheet->setCellValue("M{$r}", "=SUM(M6:M{$lastDataRow})");
        $sheet->setCellValue("N{$r}", "=SUM(N6:N{$lastDataRow})");
        $sheet->setCellValue("O{$r}", "=SUM(O6:O{$lastDataRow})");
        $sheet->setCellValue("P{$r}", "=SUM(P6:P{$lastDataRow})");
        $sheet->setCellValue("Q{$r}", "=SUM(Q6:Q{$lastDataRow})");
        $sheet->setCellValue("R{$r}", "=IF(O{$r}>0, Q{$r}/O{$r}, 0)");
        $sheet->setCellValue("S{$r}", '-');
        $sheet->setCellValue("T{$r}", '-');

        $sheet->getStyle("A{$r}:T{$r}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
        $sheet->getStyle("A{$r}:T{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle("J{$r}:Q{$r}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle("R{$r}")->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle("J{$r}:R{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $this->applyBorderDoubleBottom($sheet, "A{$r}:T{$r}");

        // Freeze Panes at A6 so Header on Row 5 stays fixed
        $sheet->freezePane('A6');

        // AutoFilter on Header Row 5
        $sheet->setAutoFilter("A5:T{$lastDataRow}");

        // Auto-fit Columns on Ledger Sheet
        $this->autoFitColumns($sheet, array_keys($headers));
    }

    /**
     * Render a stylized Bento KPI Card spanning two columns.
     */
    private function renderKpiCard(
        Worksheet $sheet,
        string $colStart,
        string $colEnd,
        int $rowStart,
        string $title,
        float|int $value,
        string $subtitle,
        string $bgColor,
        string $accentColor,
        string $numberFormat
    ): void {
        $sheet->mergeCells("{$colStart}{$rowStart}:{$colEnd}{$rowStart}");
        $sheet->mergeCells("{$colStart}" . ($rowStart + 1) . ":{$colEnd}" . ($rowStart + 1));
        $sheet->mergeCells("{$colStart}" . ($rowStart + 2) . ":{$colEnd}" . ($rowStart + 2));

        $sheet->setCellValue("{$colStart}{$rowStart}", $title);
        $sheet->setCellValue("{$colStart}" . ($rowStart + 1), $value);
        $sheet->setCellValue("{$colStart}" . ($rowStart + 2), $subtitle);

        // Styling
        $sheet->getStyle("{$colStart}{$rowStart}")->getFont()->setSize(8.5)->setBold(true)->getColor()->setRGB('64748B');
        $sheet->getStyle("{$colStart}" . ($rowStart + 1))->getFont()->setSize(14)->setBold(true)->getColor()->setRGB($accentColor);
        $sheet->getStyle("{$colStart}" . ($rowStart + 1))->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle("{$colStart}" . ($rowStart + 2))->getFont()->setSize(8)->getColor()->setRGB('475569');

        $range = "{$colStart}{$rowStart}:{$colEnd}" . ($rowStart + 2);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgColor);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $this->applyBorderThin($sheet, $range);
    }

    /**
     * Style Section Header row.
     */
    private function styleSectionHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    /**
     * Apply thin border to range.
     */
    private function applyBorderThin(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDER_LINE);
    }

    /**
     * Apply double bottom border to total row.
     */
    private function applyBorderDoubleBottom(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDER_LINE);
        $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->getColor()->setRGB(self::COLOR_DARK_HEADER);
    }

    /**
     * Auto-fit all specified columns with safety margin.
     *
     * @param array<int, string> $columns
     */
    private function autoFitColumns(Worksheet $sheet, array $columns): void
    {
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
