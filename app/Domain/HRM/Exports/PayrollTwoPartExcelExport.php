<?php

declare(strict_types=1);

namespace App\Domain\HRM\Exports;

use App\Models\Business;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollTwoPartExcelExport
{
    protected Payroll $payroll;
    protected Business $business;

    public function __construct(Payroll $payroll, ?Business $business = null)
    {
        $this->payroll = $payroll;
        $this->business = $business ?? $payroll->business ?? Business::findOrFail($payroll->business_id);
    }

    /**
     * Generate the complete 2-Sheet Excel workbook and return as StreamedResponse.
     */
    public function download(): StreamedResponse
    {
        $spreadsheet = $this->buildSpreadsheet();
        $periodStr = $this->payroll->period_year . '_' . str_pad((string) $this->payroll->period_month, 2, '0', STR_PAD_LEFT);
        $filename = "Laporan_Penggajian_{$this->business->slug}_{$periodStr}.xlsx";

        return new StreamedResponse(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * Build the 2-Sheet Spreadsheet instance.
     */
    public function buildSpreadsheet(): Spreadsheet
    {
        $this->payroll->load([
            'items' => fn ($query) => $query->orderBy('employee_name', 'asc'),
            'items.user',
        ]);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('COOCA ERP Platform')
            ->setLastModifiedBy('COOCA HRM System')
            ->setTitle("Laporan Penggajian - {$this->payroll->title}")
            ->setSubject("Penggajian Periode {$this->payroll->formatted_period}")
            ->setDescription("Laporan Penggajian Resmi 2-Sheet: Ringkasan Eksekutif dan Buku Besar Rincian.");

        // 1. Build Sheet 1: Ringkasan Eksekutif
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Ringkasan Eksekutif');
        $this->buildExecutiveSummarySheet($sheet1);

        // 2. Build Sheet 2: Buku Besar Penggajian
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Buku Besar Penggajian');
        $this->buildGeneralLedgerSheet($sheet2);

        // Set active sheet back to Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Sheet 1: Ringkasan Eksekutif & KPI Bento Cards
     */
    protected function buildExecutiveSummarySheet(Worksheet $sheet): void
    {
        $sheet->setShowGridLines(true);

        // Header Title
        $sheet->setCellValue('A1', mb_strtoupper($this->business->name));
        $sheet->setCellValue('A2', 'RINGKASAN EKSEKUTIF PENGGAJIAN BULANAN');
        $sheet->setCellValue('A3', "Periode: {$this->payroll->formatted_period}  |  Batch: {$this->payroll->payroll_number}  |  Status: " . mb_strtoupper($this->payroll->status));
        $sheet->setCellValue('A4', 'Waktu Dibuat: ' . Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB');

        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1C1C1E'));
        $sheet->getStyle('A2')->getFont()->setSize(12)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF007AFF'));
        $sheet->getStyle('A3')->getFont()->setSize(10)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF48484A'));
        $sheet->getStyle('A4')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF8E8E93'));

        // KPI Bento Metric Cards (Row 6 - Row 8)
        $kpiCards = [
            ['title' => 'TOTAL KARYAWAN', 'value' => $this->payroll->total_employees_count . ' Orang', 'colStart' => 'A', 'colEnd' => 'B'],
            ['title' => 'TOTAL TAKE-HOME PAY', 'value' => (float) $this->payroll->total_take_home_pay, 'colStart' => 'C', 'colEnd' => 'D', 'isCurrency' => true],
            ['title' => 'TOTAL BEBAN PERUSAHAAN', 'value' => (float) $this->payroll->total_company_cost, 'colStart' => 'E', 'colEnd' => 'F', 'isCurrency' => true],
            ['title' => 'TOTAL POTONGAN PPH 21', 'value' => (float) $this->payroll->total_tax_pph21, 'colStart' => 'G', 'colEnd' => 'H', 'isCurrency' => true],
        ];

        foreach ($kpiCards as $card) {
            $rangeTitle = "{$card['colStart']}6:{$card['colEnd']}6";
            $rangeVal = "{$card['colStart']}7:{$card['colEnd']}8";
            $rangeBox = "{$card['colStart']}6:{$card['colEnd']}8";

            $sheet->mergeCells($rangeTitle);
            $sheet->mergeCells($rangeVal);

            $sheet->setCellValue("{$card['colStart']}6", $card['title']);
            $sheet->setCellValue("{$card['colStart']}7", $card['value']);

            // Style Card Header
            $sheet->getStyle($rangeTitle)->getFont()->setSize(9)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF636366'));
            $sheet->getStyle($rangeTitle)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            // Style Card Value
            $valFont = $sheet->getStyle($rangeVal)->getFont()->setSize(13)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1C1C1E'));
            if (!empty($card['isCurrency'])) {
                $sheet->getStyle($rangeVal)->getNumberFormat()->setFormatCode('#,##0');
            }
            $sheet->getStyle($rangeVal)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            // Box Styling
            $sheet->getStyle($rangeBox)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F7');
            $sheet->getStyle($rangeBox)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE5E5EA');
        }

        // Breakdown Table Section (Row 11)
        $sheet->setCellValue('A11', 'RINCIAN AKUMULASI PENGELUARAN & POTONGAN PERIODE');
        $sheet->getStyle('A11')->getFont()->setSize(11)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1C1C1E'));

        // Table Header
        $sheet->setCellValue('A12', 'No');
        $sheet->setCellValue('B12', 'Komponen Keuangan');
        $sheet->setCellValue('C12', 'Kategori');
        $sheet->setCellValue('D12', 'Jumlah Akumulasi (Rp)');
        $sheet->setCellValue('E12', 'Catatan / Dasar Ketentuan');

        $sheet->getStyle('A12:E12')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A12:E12')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1C1C1E');
        $sheet->getStyle('A12:E12')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension(12)->setRowHeight(24);

        // Aggregate Totals
        $totBase = (float) $this->payroll->total_base_salary;
        $totFixed = (float) $this->payroll->total_fixed_allowances;
        $totVar = (float) $this->payroll->total_variable_allowances;
        $totOvertime = (float) $this->payroll->total_overtime_pay;
        $totComm = (float) $this->payroll->total_commissions;
        $totThr = (float) $this->payroll->total_thr_bonus;
        $totGross = (float) $this->payroll->total_gross_salary;

        $totPph = (float) $this->payroll->total_tax_pph21;
        $totBpjsTkEmp = (float) $this->payroll->total_bpjs_tk_employee;
        $totBpjsKesEmp = (float) $this->payroll->total_bpjs_kes_employee;
        $totLoans = (float) $this->payroll->total_loan_deductions;
        $totOtherDeduct = (float) $this->payroll->total_other_deductions;
        $totDeductions = (float) $this->payroll->total_deductions;

        $totBpjsTkComp = (float) $this->payroll->total_bpjs_tk_employer;
        $totBpjsKesComp = (float) $this->payroll->total_bpjs_kes_employer;
        $totNet = (float) $this->payroll->total_take_home_pay;
        $totCompCost = (float) $this->payroll->total_company_cost;

        $components = [
            ['Gaji Pokok / Upah Harian Tenaga Kerja', 'Penerimaan Bruto', $totBase, 'Kompensasi dasar seluruh staf aktif'],
            ['Tunjangan Tetap (Jabatan / Fungsional)', 'Penerimaan Bruto', $totFixed, 'Tunjangan teratur bulanan'],
            ['Tunjangan Variabel (Makan / Transport)', 'Penerimaan Bruto', $totVar, 'Tunjangan berbasis kehadiran'],
            ['Upah Kerja Lembur (Overtime)', 'Penerimaan Bruto', $totOvertime, 'Kalkulasi jam lembur disetujui'],
            ['Komisi Penjualan / Bonus Performa SPK', 'Penerimaan Bruto', $totComm, 'Insentif kinerja terintegrasi'],
            ['Tunjangan Hari Raya (THR Keagamaan)', 'Penerimaan Bruto', $totThr, 'Prorata masa kerja Permenaker 6/2016'],
            ['TOTAL PENGHASILAN BRUTO', 'Subtotal Bruto', $totGross, 'Total bruto sebelum potongan pajak & iuran'],
            ['Pajak Penghasilan PPh 21 (TER PP 58/2023)', 'Potongan Resmi', $totPph, 'Kategori TER A/B/C sesuai status PTKP'],
            ['BPJS Ketenagakerjaan Karyawan (JHT 2% + JP 1%)', 'Potongan Resmi', $totBpjsTkEmp, 'Dipotong dari gaji bruto karyawan'],
            ['BPJS Kesehatan Karyawan (1%)', 'Potongan Resmi', $totBpjsKesEmp, 'Dipotong dari gaji bruto karyawan'],
            ['Potongan Angsuran Kasbon / Pinjaman Internal', 'Potongan Pinjaman', $totLoans, 'Otomatis mengurangi sisa saldo kasbon'],
            ['Potongan Lain-Lain / Penalti Terlambat', 'Potongan Lain', $totOtherDeduct, 'Penyesuaian internal'],
            ['TOTAL POTONGAN KARYAWAN', 'Subtotal Potongan', $totDeductions, 'Total seluruh pengurang upah'],
            ['TOTAL GAJI BERSIH (TAKE-HOME PAY)', 'Dana Ditransfer', $totNet, 'Dana neto yang ditransfer ke rekening karyawan'],
            ['Kontribusi BPJS TK Perusahaan (JKK, JKM, JHT, JP)', 'Beban Benefit', $totBpjsTkComp, 'Ditanggung penuh oleh perusahaan'],
            ['Kontribusi BPJS Kesehatan Perusahaan (4%)', 'Beban Benefit', $totBpjsKesComp, 'Ditanggung penuh oleh perusahaan'],
            ['TOTAL BEBAN PENGELUARAN PERUSAHAAN', 'Beban Usaha Total', $totCompCost, 'Gross Salary + Iuran BPJS Porsi Perusahaan'],
        ];

        $r = 13;
        $num = 1;
        foreach ($components as $c) {
            $isHighlight = in_array($c[1], ['Subtotal Bruto', 'Subtotal Potongan', 'Dana Ditransfer', 'Beban Usaha Total'], true);

            $sheet->setCellValue("A{$r}", $isHighlight ? '' : $num++);
            $sheet->setCellValue("B{$r}", $c[0]);
            $sheet->setCellValue("C{$r}", $c[1]);
            $sheet->setCellValue("D{$r}", $c[2]);
            $sheet->setCellValue("E{$r}", $c[3]);

            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            if ($isHighlight) {
                $sheet->getStyle("A{$r}:E{$r}")->getFont()->setBold(true);
                if ($c[1] === 'Dana Ditransfer') {
                    $sheet->getStyle("A{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8F5E9');
                    $sheet->getStyle("B{$r}:D{$r}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF2E7D32'));
                } elseif ($c[1] === 'Beban Usaha Total') {
                    $sheet->getStyle("A{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE3F2FD');
                    $sheet->getStyle("B{$r}:D{$r}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1565C0'));
                } else {
                    $sheet->getStyle("A{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F7');
                }
            } else {
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFAFAFA');
                }
            }

            $sheet->getStyle("A{$r}:E{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_HAIR)->getColor()->setARGB('FFE5E5EA');
            $sheet->getRowDimension($r)->setRowHeight(20);
            $r++;
        }

        // Auto-fit columns
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Sheet 2: Buku Besar Rincian Penggajian Karyawan
     */
    protected function buildGeneralLedgerSheet(Worksheet $sheet): void
    {
        $sheet->setShowGridLines(true);

        // Header Title
        $sheet->setCellValue('A1', mb_strtoupper($this->business->name));
        $sheet->setCellValue('A2', 'BUKU BESAR RINCIAN PENGGAJIAN KARYAWAN');
        $sheet->setCellValue('A3', "Periode: {$this->payroll->formatted_period}  |  No. Dokumen Batch: {$this->payroll->payroll_number}");

        $sheet->getStyle('A1')->getFont()->setSize(13)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1C1C1E'));
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF007AFF'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF8E8E93'));

        // Table Column Headers (Row 5)
        $headers = [
            'A5' => 'No',
            'B5' => 'Nama Karyawan',
            'C5' => 'NIK / NPWP',
            'D5' => 'Jabatan',
            'E5' => 'Status Kerja',
            'F5' => 'PTKP',
            'G5' => 'Hari Hadir',
            'H5' => 'Jam Lembur',
            'I5' => 'Gaji Pokok (Rp)',
            'J5' => 'Tunj. Tetap (Rp)',
            'K5' => 'Tunj. Variabel (Rp)',
            'L5' => 'Upah Lembur (Rp)',
            'M5' => 'Komisi SPK (Rp)',
            'N5' => 'THR Bonus (Rp)',
            'O5' => 'Total Bruto (Rp)',
            'P5' => 'PPh 21 TER (Rp)',
            'Q5' => 'BPJS TK Staf (Rp)',
            'R5' => 'BPJS Kes Staf (Rp)',
            'S5' => 'Pot. Kasbon (Rp)',
            'T5' => 'Pot. Lain (Rp)',
            'U5' => 'Total Potongan (Rp)',
            'V5' => 'Gaji Bersih / THP (Rp)',
            'W5' => 'BPJS TK Prsh (Rp)',
            'X5' => 'BPJS Kes Prsh (Rp)',
            'Y5' => 'Beban Perusahaan (Rp)',
            'Z5' => 'Rekening Penerima',
        ];

        foreach ($headers as $cell => $txt) {
            $sheet->setCellValue($cell, $txt);
        }

        $sheet->getStyle('A5:Z5')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A5:Z5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1C1C1E');
        $sheet->getStyle('A5:Z5')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(5)->setRowHeight(28);

        // Freeze Panes below header and after employee name (Cell C6)
        $sheet->freezePane('C6');

        // AutoFilter on header row
        $sheet->setAutoFilter('A5:Z5');

        // Data Rows Loop
        $r = 6;
        $idx = 1;
        foreach ($this->payroll->items as $item) {
            $empTypeStr = match ($item->employment_type) {
                'permanent' => 'Tetap (PKWTT)',
                'contract' => 'Kontrak (PKWT)',
                default => 'Harian Lepas'
            };

            $nikOrNpwp = $item->user?->nik ?? $item->user?->npwp ?? '-';
            $bankStr = !empty($item->bank_account_number)
                ? ($item->bank_name . ' - ' . $item->bank_account_number . ' (' . ($item->bank_account_holder ?: $item->employee_name) . ')')
                : 'Tunai / Kasir';

            $sheet->setCellValue("A{$r}", $idx++);
            $sheet->setCellValue("B{$r}", $item->employee_name);
            $sheet->setCellValue("C{$r}", $nikOrNpwp);
            $sheet->setCellValue("D{$r}", $item->job_title ?: 'Staf');
            $sheet->setCellValue("E{$r}", $empTypeStr);
            $sheet->setCellValue("F{$r}", $item->tax_ptkp_status ?: 'TK/0');
            $sheet->setCellValue("G{$r}", (int) $item->days_worked);
            $sheet->setCellValue("H{$r}", (float) $item->overtime_hours);

            // Earnings
            $sheet->setCellValue("I{$r}", (float) $item->base_salary);
            $sheet->setCellValue("J{$r}", (float) $item->fixed_allowances);
            $sheet->setCellValue("K{$r}", (float) $item->variable_allowances);
            $sheet->setCellValue("L{$r}", (float) $item->overtime_pay);
            $sheet->setCellValue("M{$r}", (float) $item->commissions);
            $sheet->setCellValue("N{$r}", (float) $item->thr_bonus);

            // Gross Formula
            $sheet->setCellValue("O{$r}", "=SUM(I{$r}:N{$r})");

            // Deductions
            $sheet->setCellValue("P{$r}", (float) $item->tax_pph21);
            $sheet->setCellValue("Q{$r}", (float) $item->bpjs_tk_employee);
            $sheet->setCellValue("R{$r}", (float) $item->bpjs_kes_employee);
            $sheet->setCellValue("S{$r}", (float) $item->loan_deduction);
            $sheet->setCellValue("T{$r}", (float) $item->other_deductions);

            // Total Deductions Formula
            $sheet->setCellValue("U{$r}", "=SUM(P{$r}:T{$r})");

            // Take Home Pay Formula: Gross (O) - Deductions (U)
            $sheet->setCellValue("V{$r}", "=O{$r}-U{$r}");

            // Employer Contributions
            $sheet->setCellValue("W{$r}", (float) $item->bpjs_tk_employer);
            $sheet->setCellValue("X{$r}", (float) $item->bpjs_kes_employer);

            // Total Company Cost Formula: Gross (O) + BPJS TK Comp (W) + BPJS Kes Comp (X)
            $sheet->setCellValue("Y{$r}", "=O{$r}+W{$r}+X{$r}");

            $sheet->setCellValue("Z{$r}", $bankStr);

            // Row Formatting & Alignment
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$r}:F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$r}:H{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$r}:Y{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$r}:Y{$r}")->getNumberFormat()->setFormatCode('#,##0');

            // Zebra Striping
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:Z{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFAFAFA');
            }

            // Highlights
            $sheet->getStyle("O{$r}")->getFont()->setBold(true);
            $sheet->getStyle("U{$r}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFD32F2F'));
            $sheet->getStyle("V{$r}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF2E7D32'));
            $sheet->getStyle("Y{$r}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1565C0'));

            $sheet->getStyle("A{$r}:Z{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_HAIR)->getColor()->setARGB('FFE5E5EA');
            $sheet->getRowDimension($r)->setRowHeight(21);
            $r++;
        }

        // Summary Formula Row at Bottom
        $lastDataRow = $r - 1;
        $sheet->setCellValue("A{$r}", 'TOTAL KESELURUHAN');
        $sheet->mergeCells("A{$r}:F{$r}");

        // Formulas for totals
        foreach (['G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'] as $col) {
            $sheet->setCellValue("{$col}{$r}", "=SUM({$col}6:{$col}{$lastDataRow})");
        }

        $sheet->getStyle("A{$r}:Z{$r}")->getFont()->setBold(true);
        $sheet->getStyle("A{$r}:F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G{$r}:Y{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("G{$r}:Y{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("A{$r}:Z{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F7');
        $sheet->getStyle("A{$r}:Z{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF8E8E93');
        $sheet->getStyle("A{$r}:Z{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->getColor()->setARGB('FF1C1C1E');
        $sheet->getRowDimension($r)->setRowHeight(24);

        // Auto-fit all columns
        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
