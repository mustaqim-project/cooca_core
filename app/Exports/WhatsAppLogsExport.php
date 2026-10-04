<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Business;
use App\Models\WhatsAppMessageLog;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class WhatsAppLogsExport
{
    private const COLOR_DARK_HEADER   = '1C1C1E'; // Apple Onyx
    private const COLOR_BLUE_ACCENT   = '007AFF'; // iOS System Blue
    private const COLOR_EMERALD_BG    = 'ECFDF5'; // Light Mint
    private const COLOR_EMERALD_BORDER= '10B981'; // Emerald 500
    private const COLOR_EMERALD_TEXT  = '065F46'; // Deep Emerald
    private const COLOR_ROSE_BG       = 'FEF2F2'; // Light Rose
    private const COLOR_ROSE_BORDER   = 'F43F5E'; // Rose 500
    private const COLOR_ROSE_TEXT     = '9F1239'; // Deep Rose
    private const COLOR_SUBHEADER_BG  = 'F1F5F9'; // Slate 100
    private const COLOR_ZEBRA_BG      = 'F8FAFC'; // Slate 50
    private const COLOR_BORDER_LINE   = 'E2E8F0'; // Slate 200

    /**
     * Generate the complete 2-sheet Spreadsheet instance.
     */
    public function generate(Business $business, array $filters = [], bool $isOwner = false): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($business->name . ' - Cooca Suite')
            ->setLastModifiedBy($business->name)
            ->setTitle('Laporan Komunikasi WhatsApp')
            ->setSubject('WhatsApp Gateway & Message Delivery Audit Ledger')
            ->setDescription('Audit trail dua bagian: Ringkasan Eksekutif KPI & Rincian Log Forensik Transaksional');

        // Fetch Logs Query
        $type = $filters['type'] ?? null;
        $startDate = !empty($filters['start_date']) ? Carbon::parse($filters['start_date'])->startOfDay() : null;
        $endDate   = !empty($filters['end_date']) ? Carbon::parse($filters['end_date'])->endOfDay() : null;

        $logsQuery = WhatsAppMessageLog::where('business_id', $business->id)
            ->when(!empty($type) && in_array($type, ['receipt', 'broadcast', 'test'], true), function ($q) use ($type) {
                return $q->where('type', $type);
            })
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                return $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->latest();

        $logs = $logsQuery->get();

        // -------------------------------------------------------------
        // SHEET 1: RINGKASAN EKSEKUTIF & KPI
        // -------------------------------------------------------------
        $sheetSummary = $spreadsheet->getActiveSheet();
        $sheetSummary->setTitle('Ringkasan Eksekutif');
        $this->buildExecutiveSummarySheet($sheetSummary, $business, $logs, $filters);

        // -------------------------------------------------------------
        // SHEET 2: RINCIAN LOG LENGKAP
        // -------------------------------------------------------------
        $sheetLedger = $spreadsheet->createSheet();
        $sheetLedger->setTitle('Rincian Log Lengkap');
        $this->buildTransactionalLedgerSheet($sheetLedger, $business, $logs, $isOwner);

        // Default to first sheet
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Download the workbook as a StreamedResponse.
     */
    public function download(Business $business, array $filters = [], bool $isOwner = false): StreamedResponse
    {
        $spreadsheet = $this->generate($business, $filters, $isOwner);
        $cleanBusinessName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $business->name);
        $filename = 'Laporan_Komunikasi_WhatsApp_' . $cleanBusinessName . '_' . now()->format('Ymd_His') . '.xlsx';

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
     */
    private function buildExecutiveSummarySheet(Worksheet $sheet, Business $business, $logs, array $filters): void
    {
        $sheet->setShowGridLines(true);

        // Header Title Block
        $sheet->setCellValue('A2', strtoupper($business->name));
        $sheet->setCellValue('A3', 'LAPORAN EKSEKUTIF KOMUNIKASI & AUDIT WHATSAPP GATEWAY');

        $filterText = empty($filters['type']) ? 'Semua Kategori' : ucfirst($filters['type']);
        $sheet->setCellValue('A4', 'Filter: ' . $filterText . ' | Waktu Unduh: ' . now()->format('d/m/Y H:i:s') . ' WIB | Sistem: COOCA Enterprise');

        $sheet->getStyle('A2')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle('A3')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A4')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        // Aggregated Metrics
        $totalLogs      = $logs->count();
        $totalSent      = $logs->where('status', 'sent')->count();
        $totalFailed    = $logs->where('status', 'failed')->count();
        $successRate    = $totalLogs > 0 ? round(($totalSent / $totalLogs) * 100, 1) : 100.0;

        $receiptCount   = $logs->where('type', 'receipt')->where('status', 'sent')->count();
        $broadcastCount = $logs->where('type', 'broadcast')->where('status', 'sent')->count();

        // 4 Bento KPI Cards in Row 6..8
        // Card 1: Total Pesan Terkirim (Cols A-B)
        $this->renderKpiCard($sheet, 'A', 'B', 6, 'TOTAL PESAN TERKIRIM', $totalSent, $totalLogs . ' total diproses', 'EFF6FF', self::COLOR_BLUE_ACCENT, '#,##0');

        // Card 2: Success Rate (Cols C-D)
        $this->renderKpiCard($sheet, 'C', 'D', 6, 'DELIVERY SUCCESS RATE', $successRate / 100, $totalFailed . ' pesan gagal', self::COLOR_EMERALD_BG, self::COLOR_EMERALD_BORDER, '0.0%');

        // Card 3: Struk POS (Cols E-F)
        $this->renderKpiCard($sheet, 'E', 'F', 6, 'STRUK DIGITAL POS', $receiptCount, 'Otomatis kasir checkout', 'F0FDFA', '0D9488', '#,##0');

        // Card 4: Blast Promosi (Cols G-H)
        $this->renderKpiCard($sheet, 'G', 'H', 6, 'SIARAN BLAST PROMOSI', $broadcastCount, 'Kampanye promosi massal', 'FAF5FF', '9333EA', '#,##0');

        // Table Header: Distribusi Komunikasi
        $sheet->setCellValue('A11', 'DISTRIBUSI PENGIRIMAN PESAN BERDASARKAN KATEGORI & STATUS');
        $sheet->mergeCells('A11:F11');
        $this->styleSectionHeader($sheet, 'A11:F11');

        $headers = ['Kategori Komunikasi', 'Sukses Terkirim', 'Gagal Terkirim', 'Total Pesan', 'Tingkat Sukses (%)', 'Porsi Volume (%)'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F'];

        foreach ($headers as $idx => $label) {
            $sheet->setCellValue("{$cols[$idx]}12", $label);
            $sheet->getStyle("{$cols[$idx]}12")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$cols[$idx]}12")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$cols[$idx]}12")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if ($idx > 0) {
                $sheet->getStyle("{$cols[$idx]}12")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(12)->setRowHeight(26);

        // Data Rows
        $categories = [
            ['label' => 'Struk Kasir Digital (POS Receipt)', 'key' => 'receipt'],
            ['label' => 'Siaran Blast Promosi (Broadcast)', 'key' => 'broadcast'],
            ['label' => 'Uji Coba Sistem (Test Message)', 'key' => 'test'],
        ];

        $r = 13;
        foreach ($categories as $cat) {
            $catSent   = $logs->where('type', $cat['key'])->where('status', 'sent')->count();
            $catFailed = $logs->where('type', $cat['key'])->where('status', 'failed')->count();

            $sheet->setCellValue("A{$r}", $cat['label']);
            $sheet->setCellValue("B{$r}", $catSent);
            $sheet->setCellValue("C{$r}", $catFailed);
            $sheet->setCellValue("D{$r}", "=B{$r}+C{$r}");
            $sheet->setCellValue("E{$r}", "=IF(D{$r}>0, (B{$r}/D{$r}), 1)");
            $sheet->setCellValue("F{$r}", "=IF(D\$16>0, (D{$r}/D\$16), 0)");

            $sheet->getStyle("A{$r}")->getFont()->setSize(10);
            $sheet->getStyle("B{$r}:D{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("E{$r}:F{$r}")->getNumberFormat()->setFormatCode('0.0%');

            $sheet->getStyle("B{$r}:F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->applyBorderThin($sheet, "A{$r}:F{$r}");

            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:F{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $r++;
        }

        // Row 16: TOTAL SUMMARY ROW WITH FORMULAS
        $sheet->setCellValue("A{$r}", 'TOTAL KESELURUHAN');
        $sheet->setCellValue("B{$r}", "=SUM(B13:B15)");
        $sheet->setCellValue("C{$r}", "=SUM(C13:C15)");
        $sheet->setCellValue("D{$r}", "=SUM(D13:D15)");
        $sheet->setCellValue("E{$r}", "=IF(D{$r}>0, (B{$r}/D{$r}), 1)");
        $sheet->setCellValue("F{$r}", "=IF(D{$r}>0, 1, 0)");

        $sheet->getStyle("A{$r}:F{$r}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('0F172A');
        $sheet->getStyle("A{$r}:F{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle("B{$r}:D{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("E{$r}:F{$r}")->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle("B{$r}:F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $this->applyBorderDoubleBottom($sheet, "A{$r}:F{$r}");

        // Row 19..22: Security & Privacy Compliance Notice
        $sheet->setCellValue('A19', 'CATATAN AUDIT KEAMANAN & KEPATUHAN META CLOUD API:');
        $sheet->setCellValue('A20', '1. Seluruh nomor telepon dalam laporan ini telah melalui filter proteksi PII (Personally Identifiable Information).');
        $sheet->setCellValue('A21', '2. Pengiriman blast promosi mematuhi kebijakan Meta 24-Hour Customer Window dan menyertakan klausul opt-out resmi.');
        $sheet->setCellValue('A22', '3. Token API dan kunci enkripsi disimpan secara terenkripsi AES-256-CBC pada basis data COOCA.');

        $sheet->getStyle('A19')->getFont()->setSize(9.5)->setBold(true)->getColor()->setRGB('475569');
        $sheet->getStyle('A20:A22')->getFont()->setSize(8.5)->getColor()->setRGB('64748B');

        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Build Sheet 2: Detailed Transactional Ledger.
     */
    private function buildTransactionalLedgerSheet(Worksheet $sheet, Business $business, $logs, bool $isOwner): void
    {
        $sheet->setShowGridLines(true);

        // Header Title Block
        $sheet->setCellValue('A1', 'BUKU LOG TRANSAKSI PENGIRIMAN PESAN WHATSAPP');
        $sheet->setCellValue('A2', 'Audit Trail Forensik Komunikasi Keluar | Entitas: ' . $business->name . ' | Total: ' . $logs->count() . ' Baris Data');

        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('A2')->getFont()->setSize(9.5)->setItalic(true)->getColor()->setRGB('64748B');

        // Table Column Headers
        $headers = [
            'A' => 'ID Transaksi',
            'B' => 'Waktu Kirim (WIB)',
            'C' => 'Nama Penerima',
            'D' => 'Nomor WhatsApp',
            'E' => 'Tipe Komunikasi',
            'F' => 'Ringkasan Isi Pesan',
            'G' => 'Status',
            'H' => 'Keterangan Error / Diagnostik',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue("{$col}4", $title);
            $sheet->getStyle("{$col}4")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$col}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
            $sheet->getStyle("{$col}4")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Freeze Panes below header row (at A5)
        $sheet->freezePane('A5');

        // Phone Masking helper
        $formatPhone = function (?string $phone) use ($isOwner): string {
            if (!$phone) return '-';
            if ($isOwner) return $phone;
            $raw = trim($phone);
            $len = strlen($raw);
            if ($len <= 7) {
                return substr($raw, 0, 2) . '••••' . substr($raw, -2);
            }
            return substr($raw, 0, 4) . '••••' . substr($raw, -4);
        };

        // Type label helper
        $formatType = function (string $type): string {
            return match ($type) {
                'receipt'   => 'Struk POS',
                'broadcast' => 'Blast Promosi',
                'test'      => 'Uji Coba',
                default     => ucfirst($type),
            };
        };

        $row = 5;
        foreach ($logs as $log) {
            $maskedPhone = $formatPhone($log->recipient_phone);
            $typeLabel   = $formatType($log->type);
            $statusLabel = $log->status === 'sent' ? 'Terkirim' : 'Gagal';

            $sheet->setCellValue("A{$row}", (string) $log->id);
            $sheet->setCellValue("B{$row}", $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-');
            $sheet->setCellValue("C{$row}", $log->recipient_name ?: '-');
            $sheet->setCellValue("D{$row}", $maskedPhone);
            $sheet->setCellValue("E{$row}", $typeLabel);
            $sheet->setCellValue("F{$row}", $log->message);
            $sheet->setCellValue("G{$row}", $statusLabel);
            $sheet->setCellValue("H{$row}", $log->error_message ?: '-');

            // Cell Styles & Alignment
            $sheet->getStyle("A{$row}")->getFont()->setSize(9.5)->getColor()->setRGB('64748B');
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getFont()->getColor()->setRGB('007AFF');
            $sheet->getStyle("F{$row}")->getAlignment()->setWrapText(true);

            // Status Styling
            if ($log->status === 'sent') {
                $sheet->getStyle("G{$row}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_EMERALD_TEXT);
                $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            } else {
                $sheet->getStyle("G{$row}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_ROSE_TEXT);
                $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H{$row}")->getFont()->getColor()->setRGB(self::COLOR_ROSE_TEXT);
            }

            // Zebra Striping
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }

            $this->applyBorderThin($sheet, "A{$row}:H{$row}");
            $sheet->getRowDimension($row)->setRowHeight(24);
            $row++;
        }

        // AutoFilter on entire table
        if ($row > 5) {
            $sheet->setAutoFilter('A4:H' . ($row - 1));
        }

        // Specific column widths
        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(45);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(30);
    }

    /**
     * Render a stylized Bento KPI Card on the worksheet.
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
        $rowVal = $rowStart + 1;
        $rowSub = $rowStart + 2;

        $sheet->mergeCells("{$colStart}{$rowStart}:{$colEnd}{$rowStart}");
        $sheet->mergeCells("{$colStart}{$rowVal}:{$colEnd}{$rowVal}");
        $sheet->mergeCells("{$colStart}{$rowSub}:{$colEnd}{$rowSub}");

        $sheet->setCellValue("{$colStart}{$rowStart}", $title);
        $sheet->setCellValue("{$colStart}{$rowVal}", $value);
        $sheet->setCellValue("{$colStart}{$rowSub}", $subtitle);

        $range = "{$colStart}{$rowStart}:{$colEnd}{$rowSub}";

        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgColor);

        $sheet->getStyle("{$colStart}{$rowStart}")->getFont()->setSize(8.5)->setBold(true)->getColor()->setRGB('64748B');
        $sheet->getStyle("{$colStart}{$rowVal}")->getFont()->setSize(18)->setBold(true)->getColor()->setRGB('0F172A');
        $sheet->getStyle("{$colStart}{$rowVal}")->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle("{$colStart}{$rowSub}")->getFont()->setSize(8.5)->getColor()->setRGB('64748B');

        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($range)->getAlignment()->setIndent(1);

        // Thin outer border
        $sheet->getStyle($range)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($accentColor);
    }

    private function styleSectionHeader(Worksheet $sheet, string $cellRange): void
    {
        $sheet->getStyle($cellRange)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('0F172A');
        $sheet->getStyle($cellRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle($cellRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($cellRange)->getAlignment()->setIndent(1);
        $sheet->getRowDimension((int) filter_var($cellRange, FILTER_SANITIZE_NUMBER_INT))->setRowHeight(24);
    }

    private function applyBorderThin(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDER_LINE);
    }

    private function applyBorderDoubleBottom(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDER_LINE);
        $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->getColor()->setRGB('0F172A');
    }
}
