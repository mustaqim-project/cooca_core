<?php

declare(strict_types=1);

namespace App\Exports;

use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Domain\Report\Pos\PosReportingService;
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

    private PosReportingService $reportingService;

    public function __construct(?PosReportingService $reportingService = null)
    {
        $this->reportingService = $reportingService ?? new PosReportingService();
    }

    /**
     * Generate the complete 9-sheet Master Spreadsheet instance.
     *
     * @param Collection<int, PosOrder>|null $orders
     */
    public function generate(Business $business, mixed $filterOrOrders, ?Carbon $startDate = null, ?Carbon $endDate = null): Spreadsheet
    {
        // Polymorphic parameter support for backward compatibility
        if ($filterOrOrders instanceof PosReportFilterDTO) {
            $filter = $filterOrOrders;
            $orders = $this->reportingService->buildBaseOrdersQuery($filter)
                ->with(['customer', 'user', 'location', 'payments', 'items.product', 'posShift', 'technician'])
                ->orderBy('order_date')
                ->orderBy('created_at')
                ->get();
        } elseif ($filterOrOrders instanceof Collection) {
            $orders = $filterOrOrders;
            $sDate = $startDate ?? ($orders->first()?->order_date ? Carbon::parse($orders->first()->order_date) : now()->subDays(30));
            $eDate = $endDate ?? ($orders->last()?->order_date ? Carbon::parse($orders->last()->order_date) : now());
            $filter = new PosReportFilterDTO(
                businessId: $business->id,
                startDate: $sDate,
                endDate: $eDate
            );
        } else {
            $filter = new PosReportFilterDTO(
                businessId: $business->id,
                startDate: $startDate ?? now()->subDays(30),
                endDate: $endDate ?? now()
            );
            $orders = $this->reportingService->buildBaseOrdersQuery($filter)
                ->with(['customer', 'user', 'location', 'payments', 'items.product', 'posShift', 'technician'])
                ->get();
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($business->name . ' - Cooca Suite')
            ->setLastModifiedBy($business->name)
            ->setTitle('Master Laporan Penjualan POS')
            ->setSubject('POS Sales, Cash Flow, Margin & Operational Reconciliation Ledger')
            ->setDescription('Master Laporan 9-Sheet: Ringkasan Eksekutif, Transaksi, Produk, Kategori, Kasir, Cabang, Pembayaran, Diskon, Rekonsiliasi');

        // -------------------------------------------------------------
        // SHEET 1: RINGKASAN EKSEKUTIF (EXECUTIVE KPI & COMPOSITION)
        // -------------------------------------------------------------
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Ringkasan Eksekutif');
        $this->buildExecutiveSummarySheet($sheet1, $business, $orders, $filter);

        // -------------------------------------------------------------
        // SHEET 2: BUKU TRANSAKSI (TRANSACTION LEDGER)
        // -------------------------------------------------------------
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Buku Transaksi');
        $this->buildTransactionalLedgerSheet($sheet2, $business, $orders, $filter);

        // -------------------------------------------------------------
        // SHEET 3: KINERJA PRODUK (PRODUCT PERFORMANCE)
        // -------------------------------------------------------------
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Kinerja Produk');
        $this->buildProductPerformanceSheet($sheet3, $business, $filter);

        // -------------------------------------------------------------
        // SHEET 4: KONTRIBUSI KATEGORI (CATEGORY CONTRIBUTION)
        // -------------------------------------------------------------
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Kontribusi Kategori');
        $this->buildCategoryContributionSheet($sheet4, $business, $filter);

        // -------------------------------------------------------------
        // SHEET 5: PRODUKTIVITAS KASIR (CASHIER PRODUCTIVITY)
        // -------------------------------------------------------------
        $sheet5 = $spreadsheet->createSheet();
        $sheet5->setTitle('Produktivitas Kasir');
        $this->buildCashierProductivitySheet($sheet5, $business, $filter);

        // -------------------------------------------------------------
        // SHEET 6: PERBANDINGAN OUTLET (OUTLETS COMPARISON)
        // -------------------------------------------------------------
        $sheet6 = $spreadsheet->createSheet();
        $sheet6->setTitle('Perbandingan Outlet');
        $this->buildOutletComparisonSheet($sheet6, $business, $filter);

        // -------------------------------------------------------------
        // SHEET 7: METODE PEMBAYARAN (PAYMENT BREAKDOWN)
        // -------------------------------------------------------------
        $sheet7 = $spreadsheet->createSheet();
        $sheet7->setTitle('Metode Pembayaran');
        $this->buildPaymentMethodsSheet($sheet7, $business, $orders, $filter);

        // -------------------------------------------------------------
        // SHEET 8: DISKON & PROMOSI (DISCOUNTS & VOUCHERS)
        // -------------------------------------------------------------
        $sheet8 = $spreadsheet->createSheet();
        $sheet8->setTitle('Diskon & Promosi');
        $this->buildDiscountsPromotionsSheet($sheet8, $business, $filter);

        // -------------------------------------------------------------
        // SHEET 9: REKONSILIASI SHIFT & VOID (SHIFT RECONCILIATION & FRAUD)
        // -------------------------------------------------------------
        $sheet9 = $spreadsheet->createSheet();
        $sheet9->setTitle('Rekonsiliasi & Void');
        $this->buildShiftAndVoidAuditSheet($sheet9, $business, $filter);

        // Default active sheet: Ringkasan Eksekutif
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Download the workbook as a StreamedResponse.
     *
     * @param Collection<int, PosOrder>|PosReportFilterDTO $filterOrOrders
     */
    public function download(Business $business, mixed $filterOrOrders, ?Carbon $startDate = null, ?Carbon $endDate = null): StreamedResponse
    {
        $spreadsheet = $this->generate($business, $filterOrOrders, $startDate, $endDate);
        $cleanBusinessName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $business->name);

        $sStr = $startDate ? $startDate->format('Ymd') : ($filterOrOrders instanceof PosReportFilterDTO ? $filterOrOrders->startDate->format('Ymd') : now()->subDays(30)->format('Ymd'));
        $eStr = $endDate ? $endDate->format('Ymd') : ($filterOrOrders instanceof PosReportFilterDTO ? $filterOrOrders->endDate->format('Ymd') : now()->format('Ymd'));

        $filename = 'Master_Laporan_POS_' . $cleanBusinessName . '_' . $sStr . '-' . $eStr . '.xlsx';

        return new StreamedResponse(
            function () use ($spreadsheet): void {
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
     * SHEET 1: Executive Dashboard & Aggregations.
     *
     * @param Collection<int, PosOrder> $orders
     */
    private function buildExecutiveSummarySheet(Worksheet $sheet, Business $business, Collection $orders, PosReportFilterDTO $filter): void
    {
        $sheet->setShowGridLines(true);

        // Header Title Block
        $sheet->setCellValue('A2', strtoupper($business->name));
        $sheet->setCellValue('A3', 'MASTER LAPORAN PENJUALAN KASIR & OMZET POINT OF SALE (POS)');
        $sheet->setCellValue('A4', 'Periode: ' . $filter->startDate->translatedFormat('d M Y') . ' s/d ' . $filter->endDate->translatedFormat('d M Y') . ' | Unduh: ' . now()->format('d/m/Y H:i:s') . ' WIB | Sistem: COOCA Enterprise Single-Source-of-Truth');

        $sheet->getStyle('A2')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle('A3')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A4')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        // Aggregated Metrics
        $kpi = $this->reportingService->getKpiSummary($filter);

        // Bento KPI Cards in Row 6..8
        $this->renderKpiCard($sheet, 'A', 'B', 6, 'TOTAL OMZET PENJUALAN', $kpi->netSales, 'Omzet bersih kasir', 'EFF6FF', self::COLOR_BLUE_ACCENT, '"Rp "#,##0');
        $this->renderKpiCard($sheet, 'C', 'D', 6, 'TOTAL TRANSAKSI SELESAI', $kpi->totalOrders, 'Pesanan selesai & lunas', self::COLOR_EMERALD_BG, self::COLOR_EMERALD_BORDER, '#,##0');
        $this->renderKpiCard($sheet, 'E', 'F', 6, 'TOTAL MODAL POKOK (HPP)', $kpi->totalHpp, 'Beban Pokok Penjualan', self::COLOR_ROSE_BG, self::COLOR_ROSE_BORDER, '"Rp "#,##0');
        $this->renderKpiCard($sheet, 'G', 'H', 6, 'TOTAL LABA KOTOR', $kpi->grossProfit, 'Margin: ' . number_format($kpi->grossMarginPercent, 1) . '%', self::COLOR_EMERALD_BG, '059669', '"Rp "#,##0');
        $this->renderKpiCard($sheet, 'I', 'J', 6, 'RATA-RATA ORDER (AOV)', $kpi->averageOrderValue, 'Omzet per transaksi', self::COLOR_PURPLE_BG, self::COLOR_PURPLE_BORDER, '"Rp "#,##0');
        $this->renderKpiCard($sheet, 'K', 'L', 6, 'DISKON & PPN KELUARAN', $kpi->orderDiscount + $kpi->voucherDiscount + $kpi->pointsDiscount + $kpi->taxAmount, 'Diskon + Pajak', self::COLOR_AMBER_BG, self::COLOR_AMBER_BORDER, '"Rp "#,##0');

        // TABLE 1: KOMPOSISI PENJUALAN (BARANG FISIK VS JASA)
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

        // TABLE 2: 3-WAY RECONCILIATION SUMMARY
        $recon = $this->reportingService->reconcile($filter);
        $startReconRow = 19;
        $sheet->setCellValue("A{$startReconRow}", '2. REKONSILIASI 3-ARAH (SISTEM POS VS BUKU KAS VS FISIK)');
        $sheet->mergeCells("A{$startReconRow}:G{$startReconRow}");
        $this->styleSectionHeader($sheet, "A{$startReconRow}:G{$startReconRow}");

        $reconHeaders = ['Dimensi Rekonsiliasi', 'Nilai Sistem (POS)', 'Nilai Pembukuan (Buku Kas)', 'Nilai Fisik Kasir', 'Selisih (Discrepancy)', 'Status Integritas', 'Keterangan Audit'];
        $reconHeadRow = $startReconRow + 1;
        foreach ($reconHeaders as $idx => $label) {
            $col = $t1Cols[$idx];
            $sheet->setCellValue("{$col}{$reconHeadRow}", $label);
            $sheet->getStyle("{$col}{$reconHeadRow}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}{$reconHeadRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}{$reconHeadRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if ($idx >= 1 && $idx <= 4) {
                $sheet->getStyle("{$col}{$reconHeadRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension($reconHeadRow)->setRowHeight(24);

        $reconDataRow = $reconHeadRow + 1;
        $sheet->setCellValue("A{$reconDataRow}", 'Penerimaan Kasir Tunai & Non-Tunai');
        $sheet->setCellValue("B{$reconDataRow}", $recon->totalOrdersAmount);
        $sheet->setCellValue("C{$reconDataRow}", $recon->totalPaymentsAmount);
        $sheet->setCellValue("D{$reconDataRow}", $recon->totalShiftActualCash);
        $sheet->setCellValue("E{$reconDataRow}", $recon->orderPaymentDiscrepancy);
        $sheet->setCellValue("F{$reconDataRow}", $recon->isBalanced ? 'BALANCE / MATCH' : 'SELISIH / ANOMALI');
        $sheet->setCellValue("G{$reconDataRow}", $recon->isBalanced ? 'Seluruh pembayaran terekonsiliasi seimbang' : 'Terdapat anomali selisih');

        $sheet->getStyle("B{$reconDataRow}:E{$reconDataRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle("B{$reconDataRow}:E{$reconDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("F{$reconDataRow}")->getFont()->setBold(true)->getColor()->setRGB($recon->isBalanced ? '059669' : 'DC2626');
        $this->applyBorderThin($sheet, "A{$reconDataRow}:G{$reconDataRow}");

        // TABLE 3: PERFORMA SALURAN PENJUALAN & ONLINE FOOD DELIVERY (OJOL)
        $channelBreakdown = $this->reportingService->getSalesChannelBreakdown($filter);
        $startChannelRow = 24;
        $sheet->setCellValue("A{$startChannelRow}", '3. PERFORMA SALURAN PENJUALAN & ONLINE FOOD DELIVERY (OJOL)');
        $sheet->mergeCells("A{$startChannelRow}:L{$startChannelRow}");
        $this->styleSectionHeader($sheet, "A{$startChannelRow}:L{$startChannelRow}");

        $channelHeaders = [
            'A' => 'Saluran Penjualan / Platform',
            'B' => 'Jml Transaksi',
            'C' => 'Omzet Bruto (Rp)',
            'D' => 'Diskon (Rp)',
            'E' => 'Omzet Kasir Bersih (Rp)',
            'F' => 'MDR / Komisi (%)',
            'G' => 'Beban Komisi Ojol (Rp)',
            'H' => 'Net Payout Hak Resto (Rp)',
            'I' => 'Modal HPP (Rp)',
            'J' => 'Laba Bersih Riil (Rp)',
            'K' => 'Margin Riil (%)',
            'L' => 'Porsi Omzet (%)',
        ];
        $channelHeadRow = $startChannelRow + 1;
        foreach ($channelHeaders as $col => $label) {
            $sheet->setCellValue("{$col}{$channelHeadRow}", $label);
            $sheet->getStyle("{$col}{$channelHeadRow}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}{$channelHeadRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}{$channelHeadRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if ($col !== 'A') {
                $sheet->getStyle("{$col}{$channelHeadRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension($channelHeadRow)->setRowHeight(24);

        $cr = $channelHeadRow + 1;
        $firstChannelDataRow = $cr;
        foreach ($channelBreakdown as $ch) {
            $chName = is_array($ch) ? ($ch['channel_name'] ?? $ch['channel_label'] ?? 'Saluran POS') : ($ch->channel_name ?? $ch->channel_label ?? 'Saluran POS');
            $ordersCount = is_array($ch) ? ($ch['orders_count'] ?? $ch['total_orders'] ?? 0) : ($ch->orders_count ?? $ch->total_orders ?? 0);
            $grossSales = is_array($ch) ? ($ch['gross_sales'] ?? 0.0) : ($ch->gross_sales ?? 0.0);
            $totalDiscount = is_array($ch) ? ($ch['total_discount'] ?? 0.0) : ($ch->total_discount ?? 0.0);
            $feePercent = is_array($ch) ? ($ch['platform_fee_percent'] ?? 0.0) : ($ch->platform_fee_percent ?? 0.0);
            $feeAmount = is_array($ch) ? ($ch['platform_fee_amount'] ?? 0.0) : ($ch->platform_fee_amount ?? 0.0);
            $totalHpp = is_array($ch) ? ($ch['total_hpp'] ?? 0.0) : ($ch->total_hpp ?? 0.0);
            $contribution = is_array($ch) ? ($ch['contribution_percent'] ?? 0.0) : ($ch->contribution_percent ?? 0.0);

            $sheet->setCellValue("A{$cr}", $chName);
            $sheet->setCellValue("B{$cr}", (int) $ordersCount);
            $sheet->setCellValue("C{$cr}", (float) $grossSales);
            $sheet->setCellValue("D{$cr}", (float) $totalDiscount);
            $sheet->setCellValue("E{$cr}", "=C{$cr}-D{$cr}");
            $sheet->setCellValue("F{$cr}", ((float) $feePercent) / 100);
            $sheet->setCellValue("G{$cr}", (float) $feeAmount);
            $sheet->setCellValue("H{$cr}", "=E{$cr}-G{$cr}");
            $sheet->setCellValue("I{$cr}", (float) $totalHpp);
            $sheet->setCellValue("J{$cr}", "=H{$cr}-I{$cr}");
            $sheet->setCellValue("K{$cr}", "=IF(E{$cr}>0, J{$cr}/E{$cr}, 0)");
            $sheet->setCellValue("L{$cr}", ((float) $contribution) / 100);

            $sheet->getStyle("A{$cr}")->getFont()->setSize(10);
            $sheet->getStyle("B{$cr}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("C{$cr}:E{$cr}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("F{$cr}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("G{$cr}:J{$cr}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("K{$cr}:L{$cr}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("B{$cr}:L{$cr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderThin($sheet, "A{$cr}:L{$cr}");

            if ($cr % 2 === 0) {
                $sheet->getStyle("A{$cr}:L{$cr}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $cr++;
        }

        // Total Row Channel Table
        $lastChannelRow = $cr - 1;
        if ($lastChannelRow >= $firstChannelDataRow) {
            $sheet->setCellValue("A{$cr}", 'TOTAL KESELURUHAN SALURAN');
            $sheet->setCellValue("B{$cr}", "=SUM(B{$firstChannelDataRow}:B{$lastChannelRow})");
            $sheet->setCellValue("C{$cr}", "=SUM(C{$firstChannelDataRow}:C{$lastChannelRow})");
            $sheet->setCellValue("D{$cr}", "=SUM(D{$firstChannelDataRow}:D{$lastChannelRow})");
            $sheet->setCellValue("E{$cr}", "=SUM(E{$firstChannelDataRow}:E{$lastChannelRow})");
            $sheet->setCellValue("F{$cr}", "=IF(E{$cr}>0, G{$cr}/E{$cr}, 0)");
            $sheet->setCellValue("G{$cr}", "=SUM(G{$firstChannelDataRow}:G{$lastChannelRow})");
            $sheet->setCellValue("H{$cr}", "=SUM(H{$firstChannelDataRow}:H{$lastChannelRow})");
            $sheet->setCellValue("I{$cr}", "=SUM(I{$firstChannelDataRow}:I{$lastChannelRow})");
            $sheet->setCellValue("J{$cr}", "=SUM(J{$firstChannelDataRow}:J{$lastChannelRow})");
            $sheet->setCellValue("K{$cr}", "=IF(E{$cr}>0, J{$cr}/E{$cr}, 0)");
            $sheet->setCellValue("L{$cr}", "=IF(E{$cr}>0, 1, 0)");

            $sheet->getStyle("A{$cr}:L{$cr}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
            $sheet->getStyle("A{$cr}:L{$cr}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            $sheet->getStyle("B{$cr}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("C{$cr}:E{$cr}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("F{$cr}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("G{$cr}:J{$cr}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("K{$cr}:L{$cr}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("B{$cr}:L{$cr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderDoubleBottom($sheet, "A{$cr}:L{$cr}");
        }

        // Audit Footnote
        $fnRow = $cr + 3;
        $sheet->setCellValue("A{$fnRow}", 'CATATAN AUDIT AKUNTANSI & INTEGRITAS KASIR COOCA:');
        $sheet->setCellValue('A' . ($fnRow + 1), '1. Seluruh transaksi POS telah terekonsiliasi otomatis dengan Buku Kas (Cash Ledger) dan Jurnal Umum Berpasangan (Double-Entry General Ledger).');
        $sheet->setCellValue('A' . ($fnRow + 2), '2. Penjualan Online Food Delivery (ShopeeFood, GoFood, GrabFood) telah dipisahkan antara Omzet Bruto Kasir, Estimasi Potongan Komisi Platform (MDR), dan Net Payout riil yang diterima merchant.');
        $sheet->setCellValue('A' . ($fnRow + 3), '3. Transaksi QRIS Gateway TriPay telah disinkronkan dan memotong beban fee gateway (MDR) sesuai ketentuan Bank Indonesia.');
        $sheet->setCellValue('A' . ($fnRow + 4), '4. Pengurangan persediaan barang dan bahan baku (BOM) dihitung secara real-time berdasarkan metode Rata-Rata Tertimbang (Weighted Average Cost).');

        $sheet->getStyle("A{$fnRow}")->getFont()->setSize(9.5)->setBold(true)->getColor()->setRGB('475569');
        $sheet->getStyle('A' . ($fnRow + 1) . ':A' . ($fnRow + 4))->getFont()->setSize(8.5)->getColor()->setRGB('64748B');

        $this->autoFitColumns($sheet, ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L']);
    }

    /**
     * SHEET 2: Transactional Ledger.
     *
     * @param Collection<int, PosOrder> $orders
     */
    private function buildTransactionalLedgerSheet(Worksheet $sheet, Business $business, Collection $orders, PosReportFilterDTO $filter): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('A2', 'RINCIAN BUKU BESAR TRANSAKSI PENJUALAN KASIR (TRANSACTION LEDGER)');
        $sheet->setCellValue('A3', 'Periode: ' . $filter->startDate->translatedFormat('d M Y') . ' s/d ' . $filter->endDate->translatedFormat('d M Y') . ' | Total: ' . $orders->count() . ' Transaksi');

        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'A'  => 'No',
            'B'  => 'No. Order POS',
            'C'  => 'Tanggal',
            'D'  => 'Waktu',
            'E'  => 'Lokasi / Outlet',
            'F'  => 'Saluran Jual',
            'G'  => 'No. Ref / Ojol / Meja',
            'H'  => 'Info Kontekstual 20 Industri',
            'I'  => 'Nama Pelanggan',
            'J'  => 'Kasir / Petugas',
            'K'  => 'Tipe Order',
            'L'  => 'Subtotal Bruto (Rp)',
            'M'  => 'Diskon Order (Rp)',
            'N'  => 'Voucher Diskon (Rp)',
            'O'  => 'Poin Diskon (Rp)',
            'P'  => 'PPN Keluaran (Rp)',
            'Q'  => 'Service Charge (Rp)',
            'R'  => 'Pembulatan (Rp)',
            'S'  => 'Total Omzet Kasir (Rp)',
            'T'  => 'Komisi Platform Ojol (Rp)',
            'U'  => 'Net Payout Hak Resto (Rp)',
            'V'  => 'Modal HPP (Rp)',
            'W'  => 'Laba Bersih Riil (Rp)',
            'X'  => 'Margin Riil (%)',
            'Y'  => 'Metode Pembayaran',
            'Z'  => 'Status Pesanan',
            'AA' => 'Jumlah Cetak',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}5", $label);
            $sheet->getStyle("{$col}5")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}5")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if (in_array($col, ['L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X'], true)) {
                $sheet->getStyle("{$col}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(5)->setRowHeight(26);

        $sheet->freezePane('A6');

        $row = 6;
        $no = 1;
        $firstDataRow = 6;
        foreach ($orders as $order) {
            $orderDate = $order->order_date ? Carbon::parse($order->order_date) : ($order->created_at ?? now());
            $createdAt = $order->created_at ? Carbon::parse($order->created_at) : now();

            $customerName = $order->customer?->name ?? ($order->customer_name_guest ?: 'Pelanggan Umum');
            $cashierName  = $order->user?->name ?? 'Kasir';
            $locationName = $order->location?->name ?? 'Outlet Utama';

            // Channel label & Ojol Reference
            $channelCode = strtolower((string) ($order->sales_channel ?? 'pos'));
            $channelLabel = match ($channelCode) {
                'shopeefood' => 'ShopeeFood',
                'gofood'     => 'GoFood',
                'grabfood'   => 'GrabFood',
                'web'        => 'Toko Online / Web',
                'whatsapp'   => 'WhatsApp Order',
                default      => 'Kasir Toko (POS)',
            };
            $refOrTable = $order->external_order_ref ?: ($order->table_or_reference ?: '-');

            // 20 Industry Contextual Parser
            $industryDetails = [];
            if (!empty($order->vehicle_license_plate)) {
                $vehInfo = '🚗 ' . $order->vehicle_license_plate;
                if (!empty($order->vehicle_model)) {
                    $vehInfo .= ' (' . $order->vehicle_model . ')';
                }
                if ($order->vehicle_mileage) {
                    $vehInfo .= ' • ' . number_format((float) $order->vehicle_mileage, 0, ',', '.') . ' km';
                }
                if ($order->technician) {
                    $vehInfo .= ' • Teknisi: ' . $order->technician->name;
                }
                $industryDetails[] = $vehInfo;
            }
            if ($order->laundry_weight_kg > 0 || !empty($order->rack_location)) {
                $laundryInfo = '🧺 ';
                if ($order->laundry_weight_kg > 0) {
                    $laundryInfo .= $order->laundry_weight_kg . ' kg';
                }
                if (!empty($order->rack_location)) {
                    $laundryInfo .= ' • Rak: ' . $order->rack_location;
                }
                if ($order->laundry_status) {
                    $laundryInfo .= ' • [' . ucfirst($order->laundry_status) . ']';
                }
                $industryDetails[] = $laundryInfo;
            }

            // Apothecary / Clinic / Retail Batch & Expiry
            $batchItems = $order->items->filter(fn(PosOrderItem $i) => !empty($i->batch_number) || !empty($i->serial_number));
            if ($batchItems->isNotEmpty()) {
                $batchTexts = $batchItems->take(2)->map(function (PosOrderItem $i) {
                    $t = '';
                    if (!empty($i->batch_number)) {
                        $t .= '💊 Batch: ' . $i->batch_number;
                    }
                    if (!empty($i->serial_number)) {
                        $t .= ' 🏷️ SN: ' . $i->serial_number;
                    }
                    return trim($t);
                })->implode('; ');
                $industryDetails[] = $batchTexts;
            }

            if (empty($industryDetails) && !empty($order->notes)) {
                $industryDetails[] = '📝 ' . \Illuminate\Support\Str::limit($order->notes, 40);
            }

            $industryContext = !empty($industryDetails) ? implode(' | ', $industryDetails) : '-';

            // Platform Commission & Net Payout Calculation
            $feeRate = match ($channelCode) {
                'shopeefood', 'gofood', 'grabfood' => 0.20,
                default => 0.0,
            };
            $commissionAmount = ((float) $order->total_amount) * $feeRate;

            $pmtMethods = $order->payments->pluck('payment_method')->unique()->map(function ($pm) {
                return match ($pm) {
                    'cash' => 'Tunai',
                    'qris', 'qris_dynamic' => 'QRIS',
                    'edc_debit' => 'EDC Debit',
                    'edc_credit' => 'EDC Kredit',
                    'transfer' => 'Transfer',
                    'customer_credit' => 'Piutang',
                    'loyalty_points' => 'Poin',
                    default => ucfirst((string) $pm),
                };
            })->implode(', ');

            if ($pmtMethods === '') {
                $pmtMethods = '-';
            }

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $order->order_number);
            $sheet->setCellValue("C{$row}", $orderDate->format('d/m/Y'));
            $sheet->setCellValue("D{$row}", $createdAt->format('H:i:s'));
            $sheet->setCellValue("E{$row}", $locationName);
            $sheet->setCellValue("F{$row}", $channelLabel);
            $sheet->setCellValue("G{$row}", $refOrTable);
            $sheet->setCellValue("H{$row}", $industryContext);
            $sheet->setCellValue("I{$row}", $customerName);
            $sheet->setCellValue("J{$row}", $cashierName);
            $sheet->setCellValue("K{$row}", strtoupper(str_replace('_', ' ', $order->order_type ?? 'dine_in')));
            $sheet->setCellValue("L{$row}", (float) $order->subtotal);
            $sheet->setCellValue("M{$row}", (float) $order->discount_amount);
            $sheet->setCellValue("N{$row}", (float) $order->voucher_discount_amount);
            $sheet->setCellValue("O{$row}", (float) $order->points_discount_amount);
            $sheet->setCellValue("P{$row}", (float) $order->tax_amount);
            $sheet->setCellValue("Q{$row}", (float) $order->service_charge_amount);
            $sheet->setCellValue("R{$row}", (float) $order->rounding_amount);
            $sheet->setCellValue("S{$row}", (float) $order->total_amount);
            $sheet->setCellValue("T{$row}", (float) $commissionAmount);
            $sheet->setCellValue("U{$row}", "=S{$row}-T{$row}");
            $sheet->setCellValue("V{$row}", (float) $order->total_hpp_cost);
            $sheet->setCellValue("W{$row}", "=U{$row}-V{$row}");
            $sheet->setCellValue("X{$row}", "=IF(S{$row}>0, W{$row}/S{$row}, 0)");
            $sheet->setCellValue("Y{$row}", $pmtMethods);
            $sheet->setCellValue("Z{$row}", strtoupper($order->status));
            $sheet->setCellValue("AA{$row}", (int) $order->printed_count);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$row}:W{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("X{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("L{$row}:X{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("AA{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $this->applyBorderThin($sheet, "A{$row}:AA{$row}");

            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:AA{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }

            $row++;
        }

        // Summary Total Row
        $lastDataRow = $row - 1;
        if ($lastDataRow >= 6) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
            $sheet->mergeCells("A{$row}:K{$row}");

            $sheet->setCellValue("L{$row}", "=SUM(L6:L{$lastDataRow})");
            $sheet->setCellValue("M{$row}", "=SUM(M6:M{$lastDataRow})");
            $sheet->setCellValue("N{$row}", "=SUM(N6:N{$lastDataRow})");
            $sheet->setCellValue("O{$row}", "=SUM(O6:O{$lastDataRow})");
            $sheet->setCellValue("P{$row}", "=SUM(P6:P{$lastDataRow})");
            $sheet->setCellValue("Q{$row}", "=SUM(Q6:Q{$lastDataRow})");
            $sheet->setCellValue("R{$row}", "=SUM(R6:R{$lastDataRow})");
            $sheet->setCellValue("S{$row}", "=SUM(S6:S{$lastDataRow})");
            $sheet->setCellValue("T{$row}", "=SUM(T6:T{$lastDataRow})");
            $sheet->setCellValue("U{$row}", "=SUM(U6:U{$lastDataRow})");
            $sheet->setCellValue("V{$row}", "=SUM(V6:V{$lastDataRow})");
            $sheet->setCellValue("W{$row}", "=SUM(W6:W{$lastDataRow})");
            $sheet->setCellValue("X{$row}", "=IF(S{$row}>0, W{$row}/S{$row}, 0)");
            $sheet->setCellValue("AA{$row}", "=SUM(AA6:AA{$lastDataRow})");

            $sheet->getStyle("A{$row}:AA{$row}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
            $sheet->getStyle("A{$row}:AA{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            $sheet->getStyle("L{$row}:W{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("X{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("L{$row}:X{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("AA{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $this->applyBorderDoubleBottom($sheet, "A{$row}:AA{$row}");
        }

        $this->autoFitColumns($sheet, array_keys($headers));
    }

    /**
     * SHEET 3: Product Performance & Margin Breakdown.
     */
    private function buildProductPerformanceSheet(Worksheet $sheet, Business $business, PosReportFilterDTO $filter): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('A2', 'LAPORAN KINERJA PENJUALAN PRODUK & ANALISIS MARGIN HPP');
        $sheet->setCellValue('A3', 'Periode: ' . $filter->startDate->translatedFormat('d M Y') . ' s/d ' . $filter->endDate->translatedFormat('d M Y'));

        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'A' => 'No',
            'B' => 'Kode Produk',
            'C' => 'Nama Produk / Menu',
            'D' => 'Kategori',
            'E' => 'Tipe Komoditas',
            'F' => 'Qty Terjual',
            'G' => 'Harga Jual Satuan Rata-rata (Rp)',
            'H' => 'Total Omzet Kotor (Rp)',
            'I' => 'Diskon Item (Rp)',
            'J' => 'Total Omzet Bersih (Rp)',
            'K' => 'HPP Satuan Rata-rata (Rp)',
            'L' => 'Total Modal HPP (Rp)',
            'M' => 'Laba Kotor (Rp)',
            'N' => 'Margin Laba (%)',
            'O' => 'Kontribusi Omzet (%)',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}5", $label);
            $sheet->getStyle("{$col}5")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}5")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if (in_array($col, ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'], true)) {
                $sheet->getStyle("{$col}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(5)->setRowHeight(26);
        $sheet->freezePane('A6');

        $products = $this->reportingService->getProductPerformance($filter, 500);

        $row = 6;
        $no = 1;
        $firstDataRow = 6;
        foreach ($products as $p) {
            $sku = (string) ($p->sku ?? $p->productCode ?? $p->product_code ?? '-');
            $name = (string) ($p->product_name ?? $p->productName ?? 'Produk');
            $catName = (string) ($p->category_name ?? $p->categoryName ?? 'Tanpa Kategori');
            $type = (($p->item_type ?? $p->type ?? 'goods') === 'service') ? 'Jasa Layanan' : 'Barang Fisik';
            $qty = (float) ($p->total_qty ?? $p->totalQuantity ?? 0.0);
            $asp = (float) ($p->asp ?? $p->averageSellingPrice ?? 0.0);
            $gross = (float) ($p->gross_sales ?? $p->grossSales ?? 0.0);
            $disc = (float) ($p->discount_amount ?? $p->discountAmount ?? 0.0);
            $net = (float) ($p->net_sales ?? $p->netSales ?? 0.0);
            $cost = (float) ($p->unit_cost ?? $p->averageCostPrice ?? 0.0);
            $hpp = (float) ($p->total_hpp ?? $p->totalHpp ?? 0.0);
            $contrib = (float) ($p->revenueContributionPercent ?? $p->contribution_percent ?? 0.0);

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $sku);
            $sheet->setCellValue("C{$row}", $name);
            $sheet->setCellValue("D{$row}", $catName);
            $sheet->setCellValue("E{$row}", $type);
            $sheet->setCellValue("F{$row}", $qty);
            $sheet->setCellValue("G{$row}", $asp);
            $sheet->setCellValue("H{$row}", $gross);
            $sheet->setCellValue("I{$row}", $disc);
            $sheet->setCellValue("J{$row}", $net);
            $sheet->setCellValue("K{$row}", $cost);
            $sheet->setCellValue("L{$row}", $hpp);
            $sheet->setCellValue("M{$row}", "=J{$row}-L{$row}");
            $sheet->setCellValue("N{$row}", "=IF(J{$row}>0, M{$row}/J{$row}, 0)");
            $sheet->setCellValue("O{$row}", $contrib > 0 ? $contrib / 100 : 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("G{$row}:M{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("N{$row}:O{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("F{$row}:O{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $this->applyBorderThin($sheet, "A{$row}:O{$row}");
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:O{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $row++;
        }

        $lastDataRow = $row - 1;
        if ($lastDataRow >= 6) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN PRODUK');
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("F{$row}", "=SUM(F{$firstDataRow}:F{$lastDataRow})");
            $sheet->setCellValue("H{$row}", "=SUM(H{$firstDataRow}:H{$lastDataRow})");
            $sheet->setCellValue("I{$row}", "=SUM(I{$firstDataRow}:I{$lastDataRow})");
            $sheet->setCellValue("J{$row}", "=SUM(J{$firstDataRow}:J{$lastDataRow})");
            $sheet->setCellValue("L{$row}", "=SUM(L{$firstDataRow}:L{$lastDataRow})");
            $sheet->setCellValue("M{$row}", "=SUM(M{$firstDataRow}:M{$lastDataRow})");
            $sheet->setCellValue("N{$row}", "=IF(J{$row}>0, M{$row}/J{$row}, 0)");
            $sheet->setCellValue("O{$row}", "=IF(J{$row}>0, 1, 0)");

            $sheet->getStyle("A{$row}:O{$row}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
            $sheet->getStyle("A{$row}:O{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("G{$row}:M{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("N{$row}:O{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("F{$row}:O{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderDoubleBottom($sheet, "A{$row}:O{$row}");
        }

        $this->autoFitColumns($sheet, array_keys($headers));
    }

    /**
     * SHEET 4: Category Contribution.
     */
    private function buildCategoryContributionSheet(Worksheet $sheet, Business $business, PosReportFilterDTO $filter): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('A2', 'KONTRIBUSI PENJUALAN PER KATEGORI PRODUK');
        $sheet->setCellValue('A3', 'Periode: ' . $filter->startDate->translatedFormat('d M Y') . ' s/d ' . $filter->endDate->translatedFormat('d M Y'));

        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'A' => 'No',
            'B' => 'Nama Kategori',
            'C' => 'Volume Item Terjual (Qty)',
            'D' => 'Total Omzet Kotor (Rp)',
            'E' => 'Total Modal HPP (Rp)',
            'F' => 'Laba Kotor (Rp)',
            'G' => 'Margin Laba (%)',
            'H' => 'Porsi Omzet (%)',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}5", $label);
            $sheet->getStyle("{$col}5")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}5")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if (in_array($col, ['C', 'D', 'E', 'F', 'G', 'H'], true)) {
                $sheet->getStyle("{$col}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(5)->setRowHeight(26);

        $categories = $this->reportingService->getCategoryPerformance($filter);

        $row = 6;
        $no = 1;
        $firstDataRow = 6;
        foreach ($categories as $cat) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $cat['category_name']);
            $sheet->setCellValue("C{$row}", (float) $cat['total_quantity']);
            $sheet->setCellValue("D{$row}", (float) $cat['total_sales']);
            $sheet->setCellValue("E{$row}", (float) $cat['total_hpp']);
            $sheet->setCellValue("F{$row}", "=D{$row}-E{$row}");
            $sheet->setCellValue("G{$row}", "=IF(D{$row}>0, F{$row}/D{$row}, 0)");
            $sheet->setCellValue("H{$row}", ((float) $cat['contribution_percent']) / 100);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("C{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $this->applyBorderThin($sheet, "A{$row}:H{$row}");
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $row++;
        }

        $lastDataRow = $row - 1;
        if ($lastDataRow >= 6) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN KATEGORI');
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("C{$row}", "=SUM(C{$firstDataRow}:C{$lastDataRow})");
            $sheet->setCellValue("D{$row}", "=SUM(D{$firstDataRow}:D{$lastDataRow})");
            $sheet->setCellValue("E{$row}", "=SUM(E{$firstDataRow}:E{$lastDataRow})");
            $sheet->setCellValue("F{$row}", "=SUM(F{$firstDataRow}:F{$lastDataRow})");
            $sheet->setCellValue("G{$row}", "=IF(D{$row}>0, F{$row}/D{$row}, 0)");
            $sheet->setCellValue("H{$row}", "=IF(D{$row}>0, 1, 0)");

            $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
            $sheet->getStyle("A{$row}:H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("C{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderDoubleBottom($sheet, "A{$row}:H{$row}");
        }

        $this->autoFitColumns($sheet, array_keys($headers));
    }

    /**
     * SHEET 5: Cashier Productivity & Audit.
     */
    private function buildCashierProductivitySheet(Worksheet $sheet, Business $business, PosReportFilterDTO $filter): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('A2', 'LAPORAN PRODUKTIVITAS KASIR & STAF OPERASIONAL POS');
        $sheet->setCellValue('A3', 'Periode: ' . $filter->startDate->translatedFormat('d M Y') . ' s/d ' . $filter->endDate->translatedFormat('d M Y'));

        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'A' => 'No',
            'B' => 'Nama Kasir / Staf',
            'C' => 'Total Transaksi Selesai',
            'D' => 'Total Omzet Penjualan (Rp)',
            'E' => 'Total Diskon Diberikan (Rp)',
            'F' => 'Rata-rata Transaksi AOV (Rp)',
            'G' => 'Total Modal HPP (Rp)',
            'H' => 'Total Laba Kotor (Rp)',
            'I' => 'Margin Laba (%)',
            'J' => 'Porsi Terhadap Omzet (%)',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}5", $label);
            $sheet->getStyle("{$col}5")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}5")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if (in_array($col, ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'], true)) {
                $sheet->getStyle("{$col}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(5)->setRowHeight(26);

        $cashiers = $this->reportingService->getCashierPerformance($filter);

        $row = 6;
        $no = 1;
        $firstDataRow = 6;
        foreach ($cashiers as $c) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $c['cashier_name']);
            $sheet->setCellValue("C{$row}", (int) $c['total_orders']);
            $sheet->setCellValue("D{$row}", (float) $c['total_revenue']);
            $sheet->setCellValue("E{$row}", (float) $c['total_discount']);
            $sheet->setCellValue("F{$row}", (float) $c['average_order_value']);
            $sheet->setCellValue("G{$row}", (float) $c['total_hpp']);
            $sheet->setCellValue("H{$row}", "=D{$row}-G{$row}");
            $sheet->setCellValue("I{$row}", "=IF(D{$row}>0, H{$row}/D{$row}, 0)");
            $sheet->setCellValue("J{$row}", ((float) $c['revenue_contribution_percent']) / 100);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$row}:H{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("I{$row}:J{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("C{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $this->applyBorderThin($sheet, "A{$row}:J{$row}");
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $row++;
        }

        $lastDataRow = $row - 1;
        if ($lastDataRow >= 6) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN KASIR');
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("C{$row}", "=SUM(C{$firstDataRow}:C{$lastDataRow})");
            $sheet->setCellValue("D{$row}", "=SUM(D{$firstDataRow}:D{$lastDataRow})");
            $sheet->setCellValue("E{$row}", "=SUM(E{$firstDataRow}:E{$lastDataRow})");
            $sheet->setCellValue("F{$row}", "=IF(C{$row}>0, D{$row}/C{$row}, 0)");
            $sheet->setCellValue("G{$row}", "=SUM(G{$firstDataRow}:G{$lastDataRow})");
            $sheet->setCellValue("H{$row}", "=SUM(H{$firstDataRow}:H{$lastDataRow})");
            $sheet->setCellValue("I{$row}", "=IF(D{$row}>0, H{$row}/D{$row}, 0)");
            $sheet->setCellValue("J{$row}", "=IF(D{$row}>0, 1, 0)");

            $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
            $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$row}:H{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("I{$row}:J{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("C{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderDoubleBottom($sheet, "A{$row}:J{$row}");
        }

        $this->autoFitColumns($sheet, array_keys($headers));
    }

    /**
     * SHEET 6: Outlets Comparison.
     */
    private function buildOutletComparisonSheet(Worksheet $sheet, Business $business, PosReportFilterDTO $filter): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('A2', 'PERBANDINGAN KINERJA OUTLET & CABANG');
        $sheet->setCellValue('A3', 'Periode: ' . $filter->startDate->translatedFormat('d M Y') . ' s/d ' . $filter->endDate->translatedFormat('d M Y'));

        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'A' => 'No',
            'B' => 'Nama Outlet / Cabang',
            'C' => 'Total Transaksi',
            'D' => 'Total Omzet (Rp)',
            'E' => 'Total Modal HPP (Rp)',
            'F' => 'Laba Kotor (Rp)',
            'G' => 'Margin Laba (%)',
            'H' => 'Rata-rata Order AOV (Rp)',
            'I' => 'Porsi Terhadap Bisnis (%)',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}5", $label);
            $sheet->getStyle("{$col}5")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}5")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if (in_array($col, ['C', 'D', 'E', 'F', 'G', 'H', 'I'], true)) {
                $sheet->getStyle("{$col}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(5)->setRowHeight(26);

        $outlets = $this->reportingService->getOutletPerformance($filter);

        $row = 6;
        $no = 1;
        $firstDataRow = 6;
        foreach ($outlets as $o) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $o['outlet_name']);
            $sheet->setCellValue("C{$row}", (int) $o['total_orders']);
            $sheet->setCellValue("D{$row}", (float) $o['total_revenue']);
            $sheet->setCellValue("E{$row}", (float) $o['total_hpp']);
            $sheet->setCellValue("F{$row}", "=D{$row}-E{$row}");
            $sheet->setCellValue("G{$row}", "=IF(D{$row}>0, F{$row}/D{$row}, 0)");
            $sheet->setCellValue("H{$row}", (float) $o['average_order_value']);
            $sheet->setCellValue("I{$row}", ((float) $o['revenue_contribution_percent']) / 100);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$row}:I{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("C{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $this->applyBorderThin($sheet, "A{$row}:I{$row}");
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $row++;
        }

        $lastDataRow = $row - 1;
        if ($lastDataRow >= 6) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN OUTLET');
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("C{$row}", "=SUM(C{$firstDataRow}:C{$lastDataRow})");
            $sheet->setCellValue("D{$row}", "=SUM(D{$firstDataRow}:D{$lastDataRow})");
            $sheet->setCellValue("E{$row}", "=SUM(E{$firstDataRow}:E{$lastDataRow})");
            $sheet->setCellValue("F{$row}", "=SUM(F{$firstDataRow}:F{$lastDataRow})");
            $sheet->setCellValue("G{$row}", "=IF(D{$row}>0, F{$row}/D{$row}, 0)");
            $sheet->setCellValue("H{$row}", "=IF(C{$row}>0, D{$row}/C{$row}, 0)");
            $sheet->setCellValue("I{$row}", "=IF(D{$row}>0, 1, 0)");

            $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
            $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$row}:I{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("C{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderDoubleBottom($sheet, "A{$row}:I{$row}");
        }

        $this->autoFitColumns($sheet, array_keys($headers));
    }

    /**
     * SHEET 7: Payment Methods Breakdown.
     *
     * @param Collection<int, PosOrder> $orders
     */
    private function buildPaymentMethodsSheet(Worksheet $sheet, Business $business, Collection $orders, PosReportFilterDTO $filter): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('A2', 'RINCIAN PENERIMAAN KAS & METODE PEMBAYARAN');
        $sheet->setCellValue('A3', 'Periode: ' . $filter->startDate->translatedFormat('d M Y') . ' s/d ' . $filter->endDate->translatedFormat('d M Y'));

        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'A' => 'No',
            'B' => 'Metode Pembayaran',
            'C' => 'Frekuensi Transaksi',
            'D' => 'Total Nominal Diterima (Rp)',
            'E' => 'Estimasi Biaya MDR / Gateway (Rp)',
            'F' => 'Penerimaan Kas Bersih (Rp)',
            'G' => 'Porsi Transaksi (%)',
            'H' => 'Porsi Nominal (%)',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}5", $label);
            $sheet->getStyle("{$col}5")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}5")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if (in_array($col, ['C', 'D', 'E', 'F', 'G', 'H'], true)) {
                $sheet->getStyle("{$col}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(5)->setRowHeight(26);

        $payments = $this->reportingService->getPaymentMethodBreakdown($filter);

        $row = 6;
        $no = 1;
        $firstDataRow = 6;
        foreach ($payments as $p) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $p['method_label']);
            $sheet->setCellValue("C{$row}", (int) $p['count']);
            $sheet->setCellValue("D{$row}", (float) $p['amount']);
            $sheet->setCellValue("E{$row}", (float) $p['fee_amount']);
            $sheet->setCellValue("F{$row}", "=D{$row}-E{$row}");
            $sheet->setCellValue("G{$row}", ((float) $p['count_percentage']) / 100);
            $sheet->setCellValue("H{$row}", ((float) $p['amount_percentage']) / 100);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("C{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $this->applyBorderThin($sheet, "A{$row}:H{$row}");
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $row++;
        }

        $lastDataRow = $row - 1;
        if ($lastDataRow >= 6) {
            $sheet->setCellValue("A{$row}", 'TOTAL PENERIMAAN KAS');
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("C{$row}", "=SUM(C{$firstDataRow}:C{$lastDataRow})");
            $sheet->setCellValue("D{$row}", "=SUM(D{$firstDataRow}:D{$lastDataRow})");
            $sheet->setCellValue("E{$row}", "=SUM(E{$firstDataRow}:E{$lastDataRow})");
            $sheet->setCellValue("F{$row}", "=SUM(F{$firstDataRow}:F{$lastDataRow})");
            $sheet->setCellValue("G{$row}", "=IF(C{$row}>0, 1, 0)");
            $sheet->setCellValue("H{$row}", "=IF(D{$row}>0, 1, 0)");

            $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
            $sheet->getStyle("A{$row}:H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("C{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderDoubleBottom($sheet, "A{$row}:H{$row}");
        }

        $this->autoFitColumns($sheet, array_keys($headers));
    }

    /**
     * SHEET 8: Discounts & Promotions Analytics.
     */
    private function buildDiscountsPromotionsSheet(Worksheet $sheet, Business $business, PosReportFilterDTO $filter): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('A2', 'AUDIT POTONGAN HARGA, DISKON & PROMOSI POS');
        $sheet->setCellValue('A3', 'Periode: ' . $filter->startDate->translatedFormat('d M Y') . ' s/d ' . $filter->endDate->translatedFormat('d M Y'));

        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'A' => 'No',
            'B' => 'Kategori Diskon / Promosi',
            'C' => 'Frekuensi Digunakan',
            'D' => 'Total Nilai Potongan Diskon (Rp)',
            'E' => 'Porsi Terhadap Total Diskon (%)',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}5", $label);
            $sheet->getStyle("{$col}5")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}5")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if (in_array($col, ['C', 'D', 'E'], true)) {
                $sheet->getStyle("{$col}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(5)->setRowHeight(26);

        $discounts = $this->reportingService->getDiscountBreakdown($filter);

        $row = 6;
        $no = 1;
        $firstDataRow = 6;
        foreach ($discounts as $d) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $d['type_label']);
            $sheet->setCellValue("C{$row}", (int) $d['count']);
            $sheet->setCellValue("D{$row}", (float) $d['total_amount']);
            $sheet->setCellValue("E{$row}", ((float) $d['percentage_of_total_discount']) / 100);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $this->applyBorderThin($sheet, "A{$row}:E{$row}");
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $row++;
        }

        $lastDataRow = $row - 1;
        if ($lastDataRow >= 6) {
            $sheet->setCellValue("A{$row}", 'TOTAL SELURUH POTONGAN DISKON');
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("C{$row}", "=SUM(C{$firstDataRow}:C{$lastDataRow})");
            $sheet->setCellValue("D{$row}", "=SUM(D{$firstDataRow}:D{$lastDataRow})");
            $sheet->setCellValue("E{$row}", "=IF(D{$row}>0, 1, 0)");

            $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
            $sheet->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderDoubleBottom($sheet, "A{$row}:E{$row}");
        }

        $this->autoFitColumns($sheet, array_keys($headers));
    }

    /**
     * SHEET 9: Shift Cash Reconciliation & Void/Fraud Audit.
     */
    private function buildShiftAndVoidAuditSheet(Worksheet $sheet, Business $business, PosReportFilterDTO $filter): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('A2', 'REKONSILIASI KAS SHIFT & LOG AUDIT PEMBATALAN (VOID/FRAUD)');
        $sheet->setCellValue('A3', 'Periode: ' . $filter->startDate->translatedFormat('d M Y') . ' s/d ' . $filter->endDate->translatedFormat('d M Y'));

        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        // SECTION 1: SHIFT RECONCILIATION
        $sheet->setCellValue('A5', 'BAGIAN 1: REKONSILIASI KAS REGISTER SHIFT KASIR');
        $sheet->mergeCells('A5:J5');
        $this->styleSectionHeader($sheet, 'A5:J5');

        $shiftHeaders = [
            'A' => 'No',
            'B' => 'No. Shift',
            'C' => 'Nama Kasir',
            'D' => 'Lokasi Outlet',
            'E' => 'Waktu Buka Shift',
            'F' => 'Waktu Tutup Shift',
            'G' => 'Modal Awal (Rp)',
            'H' => 'Penjualan Tunai Sistem (Rp)',
            'I' => 'Kas Fisik Dihitung (Rp)',
            'J' => 'Selisih Kas (Discrepancy Rp)',
        ];

        foreach ($shiftHeaders as $col => $label) {
            $sheet->setCellValue("{$col}6", $label);
            $sheet->getStyle("{$col}6")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}6")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}6")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if (in_array($col, ['G', 'H', 'I', 'J'], true)) {
                $sheet->getStyle("{$col}6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(6)->setRowHeight(24);

        $shifts = $this->reportingService->getShiftReconciliationList($filter);

        $row = 7;
        $no = 1;
        $firstShiftRow = 7;
        foreach ($shifts as $s) {
            $openedAt = (string) ($s['opened_at'] ?? '-');
            $closedAt = (string) ($s['closed_at'] ?? 'Masih Terbuka');
            $shiftNum = (string) ($s['shift_number'] ?? $s['shift_id'] ?? 'SHIFT');

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $shiftNum);
            $sheet->setCellValue("C{$row}", $s['cashier_name'] ?? 'Kasir');
            $sheet->setCellValue("D{$row}", $s['location_name'] ?? 'Outlet');
            $sheet->setCellValue("E{$row}", $openedAt);
            $sheet->setCellValue("F{$row}", $closedAt);
            $sheet->setCellValue("G{$row}", (float) ($s['opening_cash'] ?? 0.0));
            $sheet->setCellValue("H{$row}", (float) ($s['total_cash_sales'] ?? $s['closing_cash_expected'] ?? 0.0));
            $sheet->setCellValue("I{$row}", (float) ($s['closing_cash_actual'] ?? 0.0));
            $sheet->setCellValue("J{$row}", (float) ($s['cash_difference'] ?? 0.0));

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}:J{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $this->applyBorderThin($sheet, "A{$row}:J{$row}");
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $row++;
        }

        $lastShiftRow = $row - 1;
        if ($lastShiftRow >= 7) {
            $sheet->setCellValue("A{$row}", 'TOTAL REKONSILIASI SHIFT');
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("G{$row}", "=SUM(G{$firstShiftRow}:G{$lastShiftRow})");
            $sheet->setCellValue("H{$row}", "=SUM(H{$firstShiftRow}:H{$lastShiftRow})");
            $sheet->setCellValue("I{$row}", "=SUM(I{$firstShiftRow}:I{$lastShiftRow})");
            $sheet->setCellValue("J{$row}", "=SUM(J{$firstShiftRow}:J{$lastShiftRow})");

            $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
            $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            $sheet->getStyle("G{$row}:J{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderDoubleBottom($sheet, "A{$row}:J{$row}");
        }

        // SECTION 2: VOID / FRAUD AUDIT LOG
        $startVoidRow = $row + 3;
        $sheet->setCellValue("A{$startVoidRow}", 'BAGIAN 2: LOG AUDIT TRANSAKSI VOID / DIBATALKAN');
        $sheet->mergeCells("A{$startVoidRow}:H{$startVoidRow}");
        $this->styleSectionHeader($sheet, "A{$startVoidRow}:H{$startVoidRow}");

        $voidHeaders = [
            'A' => 'No',
            'B' => 'No. Order POS',
            'C' => 'Tanggal & Waktu Void',
            'D' => 'Kasir / Pembuat Nota',
            'E' => 'Supervisor Otorisasi',
            'F' => 'Alasan Pembatalan (Reason)',
            'G' => 'Nominal Dibatalkan (Rp)',
            'H' => 'Status Cetak',
        ];

        $voidHeadRow = $startVoidRow + 1;
        foreach ($voidHeaders as $col => $label) {
            $sheet->setCellValue("{$col}{$voidHeadRow}", $label);
            $sheet->getStyle("{$col}{$voidHeadRow}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}{$voidHeadRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}{$voidHeadRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if ($col === 'G') {
                $sheet->getStyle("{$col}{$voidHeadRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension($voidHeadRow)->setRowHeight(24);

        $voidOrders = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_VOIDED)
            ->whereDate('order_date', '>=', $filter->startDate->toDateString())
            ->whereDate('order_date', '<=', $filter->endDate->toDateString())
            ->with(['user', 'voidedByUser'])
            ->latest('voided_at')
            ->get();

        $vr = $voidHeadRow + 1;
        $vno = 1;
        $firstVoidRow = $vr;
        foreach ($voidOrders as $vo) {
            $voidedAt = $vo->voided_at ? Carbon::parse($vo->voided_at)->format('d/m/Y H:i:s') : '-';

            $sheet->setCellValue("A{$vr}", $vno++);
            $sheet->setCellValue("B{$vr}", $vo->order_number);
            $sheet->setCellValue("C{$vr}", $voidedAt);
            $sheet->setCellValue("D{$vr}", $vo->user?->name ?? 'Kasir');
            $sheet->setCellValue("E{$vr}", $vo->voidedByUser?->name ?? 'Supervisor');
            $sheet->setCellValue("F{$vr}", $vo->void_reason ?? 'Tanpa Keterangan');
            $sheet->setCellValue("G{$vr}", (float) $vo->total_amount);
            $sheet->setCellValue("H{$vr}", ((int) $vo->printed_count > 0) ? 'VOID SETELAH CETAK (' . $vo->printed_count . 'x)' : 'Belum Pernah Dicetak');

            $sheet->getStyle("A{$vr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$vr}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$vr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            if ((int) $vo->printed_count > 0) {
                $sheet->getStyle("H{$vr}")->getFont()->setBold(true)->getColor()->setRGB('DC2626');
            }

            $this->applyBorderThin($sheet, "A{$vr}:H{$vr}");
            if ($vr % 2 === 0) {
                $sheet->getStyle("A{$vr}:H{$vr}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $vr++;
        }

        $lastVoidRow = $vr - 1;
        if ($lastVoidRow >= $firstVoidRow) {
            $sheet->setCellValue("A{$vr}", 'TOTAL NOMINAL VOID');
            $sheet->mergeCells("A{$vr}:F{$vr}");
            $sheet->setCellValue("G{$vr}", "=SUM(G{$firstVoidRow}:G{$lastVoidRow})");

            $sheet->getStyle("A{$vr}:H{$vr}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
            $sheet->getStyle("A{$vr}:H{$vr}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            $sheet->getStyle("G{$vr}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$vr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderDoubleBottom($sheet, "A{$vr}:H{$vr}");
        }

        $this->autoFitColumns($sheet, array_keys($shiftHeaders));
    }

    /**
     * Render a Bento-style KPI Card in Excel.
     */
    private function renderKpiCard(
        Worksheet $sheet,
        string $colStart,
        string $colEnd,
        int $startRow,
        string $title,
        float|int $value,
        string $subtitle,
        string $bgColor,
        string $borderColor,
        string $numFormat = '"Rp "#,##0'
    ): void {
        $r1 = $startRow;
        $r2 = $startRow + 1;
        $r3 = $startRow + 2;

        $sheet->mergeCells("{$colStart}{$r1}:{$colEnd}{$r1}");
        $sheet->mergeCells("{$colStart}{$r2}:{$colEnd}{$r2}");
        $sheet->mergeCells("{$colStart}{$r3}:{$colEnd}{$r3}");

        $sheet->setCellValue("{$colStart}{$r1}", $title);
        $sheet->setCellValue("{$colStart}{$r2}", $value);
        $sheet->setCellValue("{$colStart}{$r3}", $subtitle);

        $sheet->getStyle("{$colStart}{$r1}")->getFont()->setSize(8.5)->setBold(true)->getColor()->setRGB('64748B');
        $sheet->getStyle("{$colStart}{$r2}")->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('0F172A');
        $sheet->getStyle("{$colStart}{$r2}")->getNumberFormat()->setFormatCode($numFormat);
        $sheet->getStyle("{$colStart}{$r3}")->getFont()->setSize(8)->setItalic(true)->getColor()->setRGB('475569');

        $range = "{$colStart}{$r1}:{$colEnd}{$r3}";
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgColor);

        $borderStyle = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['rgb' => $borderColor],
                ],
            ],
        ];
        $sheet->getStyle($range)->applyFromArray($borderStyle);

        $sheet->getRowDimension($r1)->setRowHeight(16);
        $sheet->getRowDimension($r2)->setRowHeight(22);
        $sheet->getRowDimension($r3)->setRowHeight(16);
    }

    /**
     * Format a section header band.
     */
    private function styleSectionHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setSize(10.5)->setBold(true)->getColor()->setRGB('0F172A');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension((int) preg_replace('/\D/', '', $range))->setRowHeight(22);
    }

    /**
     * Apply thin border to range.
     */
    private function applyBorderThin(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => self::COLOR_BORDER_LINE],
                ],
            ],
        ]);
    }

    /**
     * Apply double bottom border to total row.
     */
    private function applyBorderDoubleBottom(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'top'    => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => self::COLOR_DARK_HEADER],
                ],
                'bottom' => [
                    'borderStyle' => Border::BORDER_DOUBLE,
                    'color'       => ['rgb' => self::COLOR_DARK_HEADER],
                ],
            ],
        ]);
    }

    /**
     * Auto-fit columns with safety minimum padding.
     *
     * @param array<int, string> $cols
     */
    private function autoFitColumns(Worksheet $sheet, array $cols): void
    {
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
