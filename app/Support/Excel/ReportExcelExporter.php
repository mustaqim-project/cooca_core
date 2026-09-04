<?php

declare(strict_types=1);

namespace App\Support\Excel;

use App\Domain\Report\FinancialReportService;
use App\Domain\Report\ReportingService;
use App\Models\Business;
use Carbon\Carbon;

/**
 * Penyusun workbook Excel laporan (export detail) dan template..
 */
final class ReportExcelExporter
{
    public function __construct(
        private readonly FinancialReportService $financialReportService = new FinancialReportService,
        private readonly ReportingService $reportingService = new ReportingService
    ) {}

    /**
     * Susun workbook laporan per jenis..
     * Jenis: income_statement | cash_flow | aging | stock_valuation | hpp..
     */
    public function buildReport(string $type, Business $business, Carbon $startDate, Carbon $endDate): XlsxWriter
    {
        $writer = (new XlsxWriter)
            ->setTitle('Laporan ' . $business->name)
            ->setCreator('Cooca Core');

        match ($type) {
            'cash_flow' => $this->buildCashFlow($writer, $business, $startDate, $endDate),
            'aging' => $this->buildAging($writer, $business),
            'stock_valuation' => $this->buildStock($writer, $business),
            'hpp' => $this->buildHpp($writer),
            default => $this->buildIncomeStatement($writer, $business, $startDate, $endDate),
        };

        return $writer;

    }

    /** ---------------------------------------------------------------- *
     * 1. Laba Rugi (Income Statement)
     * ------------------------------------------------------------------ */

    private function buildIncomeStatement(XlsxWriter $writer, Business $business, Carbon $startDate, Carbon $endDate): void
    {
        $summary = $this->financialReportService->getIncomeStatement($business, $startDate, $endDate);
        $detail = $this->financialReportService->getIncomeStatementDetail($business, $startDate, $endDate);

        $rows = [
            [['v' => 'LAPORAN LABA RUGI (INCOME STATEMENT)', 's' => XlsxWriter::STYLE_TITLE]],
            [['v' => 'Periode: ' . $summary['period']['label'], 's' => XlsxWriter::STYLE_BOLD]],
            [['v' => 'Bisnis: ' . $business->name . '   Dicetak: ' . date('d/m/Y H:i')]],

            [['v' => 'PENDAPATAN / PENJUALAN', 's' => XlsxWriter::STYLE_SECTION]],
            ['Penjualan Kasir POS (Kotor)', $summary['revenues']['pos_gross_sales']],
            ['Penjualan Faktur Invoice (Kotor)', $summary['revenues']['invoice_gross_sales']],
            [['v' => 'Total Penjualan Kotor', 's' => XlsxWriter::STYLE_BOLD], ['v' => $summary['revenues']['total_gross_sales'], 's' => XlsxWriter::STYLE_TOTAL]],
            ['Potongan Diskon & Voucher', $summary['revenues']['total_discounts']],
            ['Retur Penjualan (Pengurang)', $summary['revenues']['sales_returns']],
            [['v' => 'TOTAL PENDAPATAN BERSIH', 's' => XlsxWriter::STYLE_TOTAL], ['v' => $summary['revenues']['net_sales'], 's' => XlsxWriter::STYLE_TOTAL]],

            [['v' => 'HARGA POKOK PENJUALAN (HPP / COGS)', 's' => XlsxWriter::STYLE_SECTION]],
            ['HPP Penjualan Kasir POS', $summary['cogs']['pos_cogs']],
            ['HPP Penjualan Faktur Invoice', $summary['cogs']['invoice_cogs']],
            ['Pemulihan HPP Retur (Pengurang)', $summary['cogs']['returns_cogs_recovery']],
            [['v' => 'TOTAL HPP BARANG TERJUAL', 's' => XlsxWriter::STYLE_TOTAL], ['v' => $summary['cogs']['total_cogs'], 's' => XlsxWriter::STYLE_TOTAL]],

            [['v' => 'LABA KOTOR (GROSS PROFIT)', 's' => XlsxWriter::STYLE_TOTAL], ['v' => $summary['gross_profit']['amount'], 's' => XlsxWriter::STYLE_TOTAL]],
            [['v' => 'Margin Laba Kotor (%)', 's' => XlsxWriter::STYLE_BOLD], ['v' => $summary['gross_profit']['margin'] . '%']],

            [['v' => 'BEBAN OPERASIONAL (EXPENSES)', 's' => XlsxWriter::STYLE_SECTION]],
        ];

        foreach ($summary['expenses']['by_category'] as $cat => $amount) {
            $rows[] = ['Beban: ' . $cat, $amount];
        }

        $rows[] = [['v' => 'TOTAL BEBAN OPERASIONAL', 's' => XlsxWriter::STYLE_TOTAL], ['v' => $summary['expenses']['total'], 's' => XlsxWriter::STYLE_TOTAL]];
        $rows[] = [];
        $rows[] = [['v' => 'LABA BERSIH OPERASIONAL (NET PROFIT)', 's' => XlsxWriter::STYLE_TOTAL], ['v' => $summary['net_profit']['amount'], 's' => XlsxWriter::STYLE_TOTAL]];
        $rows[] = [['v' => 'Margin Laba Bersih (%)', 's' => XlsxWriter::STYLE_BOLD], ['v' => $summary['net_profit']['margin'] . '%']];

        $writer->addSheet('Ringkasan', $rows, [
            'widths' => ['A' => 46, 'B' => 18],
            'merge' => ['A1:C1', 'A2:C2', 'A3:C3'],
        ]);

        // [DETAIL_SHEETS]
        $writer->addSheet('Detail POS', $this->posRows($detail['pos_orders']), $this->tableOptions(
            ['A' => 20,'B' =>  ​18,'C' =>  ​24,'D' =>  ​16,'E' =>  ​14,'F' =>  ​14,'G' =>  ​14,'H' =>  ​14,'I' =>  ​14],
            count($detail['pos_orders']),
            'I'
        ));
        $writer->addSheet('Detail Invoice', $this->invoiceRows($detail['invoices']), $this->tableOptions(
            ['A' => 20,'B' =>  ​14,'C' =>  ​24,'D' =>  ​16,'E' =>  ​14,'F' =>  ​14,'G' =>  ​14,'H' =>  ​14,'I' =>  ​14,'J' =>  ​14],
            count($detail['invoices']),
            'J'
        ));
        $writer->addSheet('Detail Retur', $this->returnRows($detail['returns']), $this->tableOptions(
            ['A' => 20,'B' =>  ​14,'C' =>  ​16,'D' =>  ​18,'E' =>  ​14,'F' =>  ​30],
            count($detail['returns']),
            'F'
        ));
        $writer->addSheet('Detail Beban', $this->expenseRows($detail['expenses']), $this->tableOptions(
            ['A' => 20,'B' =>  ​14,'C' =>  ​18,'D' =>  ​16,'E' =>  ​14,'F' =>  ​30],
            count($detail['expenses']),
            'F'
        ));
    }

    private function posRows(array $orders): array
    {
        $rows = [[
            $this->headerCell('No. Order'), $this->headerCell('Tanggal'), $this->headerCell('Pelanggan'), $this->headerCell('Status'),
            $this->headerCell('Subtotal'), $this->headerCell('Diskon'), $this->headerCell('Pajak'), $this->headerCell('Service'), $this->headerCell('Total'),
        ]];

        foreach ($orders as $o) {
            $rows[] = [
                ['v' => $o['order_number']], ['v' => $o['order_date']], ['v' => $o['customer']], ['v' => $o['status']],
                ['v' => $o['subtotal']], ['v' => $o['discount']], ['v' => $o['tax']], ['v' => $o['service_fee']], ['v' => $o['total']],
            ];
        }

        $rows[] = [
            ['v' => 'TOTAL', 's' => XlsxWriter::STYLE_TOTAL], '', '', '',
            ['v' => array_sum(array_column($orders, 'subtotal')), 's' => XlsxWriter::STYLE_TOTAL],
            ['v' => array_sum(array_column($orders, 'discount')), 's' => XlsxWriter::STYLE_TOTAL],
            ['v' => array_sum(array_column($orders, 'tax')), 's' => XlsxWriter::STYLE_TOTAL],
            ['v' => array_sum(array_column($orders, 'service_fee')), 's' => XlsxWriter::STYLE_TOTAL],
            ['v' => array_sum(array_column($orders, 'total')), 's' => XlsxWriter::STYLE_TOTAL],
        ];

        return $rows;


    }

    private function invoiceRows(array $invoices): array
    {
        $rows = [[
            $this->headerCell('No. Invoice'), $this->headerCell('Tanggal'), $this->headerCell('Pelanggan'), $this->headerCell('Status'),
            $this->headerCell('Subtotal'), $this->headerCell('Diskon'), $this->headerCell('Pajak'), $this->headerCell('Total'), $this->headerCell('Dibayar'), $this->headerCell('Sisa'),
        ]];

        foreach ($invoices as $inv) {
            $rows[] = [
                ['v' => $inv['invoice_number']], ['v' => $inv['invoice_date']], ['v' => $inv['customer']], ['v' => $inv['status']],
                ['v' => $inv['subtotal']], ['v' => $inv['discount']], ['v' => $inv['tax']], ['v' => $inv['total']], ['v' => $inv['paid']], ['v' => $inv['balance_due']],
            ];
        }

        $rows[] = [
            ['v' => 'TOTAL', 's' => XlsxWriter::STYLE_TOTAL], '', '', '',
            ['v' => array_sum(array_column($invoices, 'subtotal')), 's' => XlsxWriter::STYLE_TOTAL],
            ['v' => array_sum(array_column($invoices, 'discount')), 's' => XlsxWriter::STYLE_TOTAL],
            ['v' => array_sum(array_column($invoices, 'tax')), 's' => XlsxWriter::STYLE_TOTAL],
            ['v' => array_sum(array_column($invoices, 'total')), 's' => XlsxWriter::STYLE_TOTAL],
            ['v' => array_sum(array_column($invoices, 'paid')), 's' => XlsxWriter::STYLE_TOTAL],
            ['v' => array_sum(array_column($invoices, 'balance_due')), 's' => XlsxWriter::STYLE_TOTAL],
        ];

        return $rows;


    }

    private function returnRows(array $returns): array
    {
        $rows = [[
            $this->headerCell('No. Retur'), $this->headerCell('Tanggal'), $this->headerCell('Status'), $this->headerCell('Metode Refund'),
            $this->headerCell('Total'), $this->headerCell('Alasan'),
        ]];

        foreach ($returns as $r) {
            $rows[] = [
                ['v' => $r['return_number']], ['v' => $r['return_date']], ['v' => $r['status']], ['v' => $r['refund_method']],
                ['v' => $r['total_amount']], ['v' => $r['reason']],
            ];
        }

        $rows[] = [
            ['v' => 'TOTAL', 's' => XlsxWriter::STYLE_TOTAL], '', '',
            ['v' => array_sum(array_column($returns, 'total_amount')), 's' => XlsxWriter::STYLE_TOTAL],
        ];

        return $rows;


    }

    private function expenseRows(array $expenses): array
    {
        $rows = [[
            $this->headerCell('No. Beban'), $this->headerCell('Tanggal'), $this->headerCell('Kategori'), $this->headerCell('Metode'),
            $this->headerCell('Jumlah'), $this->headerCell('Keterangan'),
        ]];

        foreach ($expenses as $e) {
            $rows[] = [
                ['v' => $e['expense_number']], ['v' => $e['expense_date']], ['v' => $e['category']], ['v' => $e['payment_method']],
                ['v' => $e['amount']], ['v' => $e['description']],
            ];
        }

        $rows[] = [
            ['v' => 'TOTAL', 's' => XlsxWriter::STYLE_TOTAL], '', '',
            ['v' => array_sum(array_column($expenses, 'amount')), 's' => XlsxWriter::STYLE_TOTAL],
        ];

        return $rows;


    }

    private function headerCell(string $label): array
    {
        return ['v' => $label, 's' => XlsxWriter::STYLE_HEADER];
    }

    private function tableOptions(array $widths, int $dataCount, string $lastCol): array
    {
        return [
            'widths' => $widths,
            'freeze' => 'A2',
            'autoFilter' => 'A1:' . $lastCol . ($dataCount + 1),
        ];
    }

    /** ------------------------------------------------------------------ *
     * 2. Arus Kas (Cash Flow)
     * ------------------------------------------------------------------ */

    private function buildCashFlow(XlsxWriter $writer, Business $business, Carbon $startDate, Carbon $endDate): void
    {
        $summary = $this->financialReportService->getCashFlowStatement($business, $startDate, $endDate);
        $detail = $this->financialReportService->getCashFlowDetail($business, $startDate, $endDate);

        $rows = [
            [['v' => 'LAPORAN ARUS KAS (CASH FLOW)', 's' => XlsxWriter::STYLE_TITLE]],
            [['v' => 'Periode: ' . $summary['period']['label'], 's' => XlsxWriter::STYLE_BOLD]],
            [['v' => 'Bisnis: ' . $business->name . '   Dicetak: ' . date('d/m/Y H:i')]],

            [['v' => 'ARUS KAS MASUK (INFLOWS)', 's' => XlsxWriter::STYLE_SECTION]],
            ['Penerimaan Kasir POS', $summary['inflows']['pos_payments']],
            ['Pelunasan Piutang Invoice', $summary['inflows']['invoice_payments']],
            ['Kas Masuk Langsung', $summary['inflows']['direct_cash_in']],
            [['v' => 'TOTAL ARUS KAS MASUK', 's' => XlsxWriter::STYLE_TOTAL], ['v' => $summary['inflows']['total'], 's' => XlsxWriter::STYLE_TOTAL]],

            [['v' => 'ARUS KAS KELUAR (OUTFLOWS)', 's' => XlsxWriter::STYLE_SECTION]],
            ['Bayar Hutang Supplier (AP)', $summary['outflows']['supplier_payments']],
            ['Beban Operasional', $summary['outflows']['expenses']],
            ['Refund Kas Retur', $summary['outflows']['cash_refunds']],
            ['Kas Keluar Langsung', $summary['outflows']['direct_cash_out']],
            [['v' => 'TOTAL ARUS KAS KELUAR', 's' => XlsxWriter::STYLE_TOTAL], ['v' => $summary['outflows']['total'], 's' => XlsxWriter::STYLE_TOTAL]],

            [['v' => 'ARUS KAS BERSIH (NET CASH FLOW)', 's' => XlsxWriter::STYLE_TOTAL], ['v' => $summary['net_cash_flow'], 's' => XlsxWriter::STYLE_TOTAL]],
            [['v' => 'TOTAL SALDO KAS & BANK TERKINI', 's' => XlsxWriter::STYLE_TOTAL], ['v' => $summary['accounts']['total_balance'], 's' => XlsxWriter::STYLE_TOTAL]],
        ];

        $writer->addSheet('Ringkasan Arus Kas', $rows, [
            'widths' => ['A' => 38,'B' =>  ​18],
            'merge' => ['A1:C1', 'A2:C2', 'A3:C3'],
        ]);

        $transactions = $detail['transactions'];
        $dataCount = count($transactions);
        $txRows = [[
            $this->headerCell('No.'), $this->headerCell('Tanggal'), $this->headerCell('Jenis Arus'), $this->headerCell('Referensi'),
            $this->headerCell('Metode'), $this->headerCell('Keterangan'), $this->headerCell('Masuk'), $this->headerCell('Keluar'),
        ]];

        $totalIn = 0.0;
        $totalOut = 0.0;

        foreach ($transactions as $i => $tx) {
            $isIn = ($tx['arah'] ?? 'in') === 'in';
            $totalIn += $isIn ? (float) $tx['jumlah'] : 0.0;
            $totalOut += $isIn ? 0.0 : (float) $tx['jumlah'];

            $txRows[] = [
                ['v' => $i + 1], ['v' => $tx['tanggal']], ['v' => $tx['jenis']], ['v' => $tx['referensi']],
                ['v' => $tx['metode']], ['v' => $tx['keterangan']],
                $isIn ? ['v' => $tx['jumlah']] : '',
                $isIn ? '' : ['v' => $tx['jumlah']],
            ];
        }

        $txRows[] = [
            ['v' => 'TOTAL', 's' => XlsxWriter::STYLE_TOTAL], '', '', '', '',
            ['v' => $totalIn, 's' => XlsxWriter::STYLE_TOTAL], ['v' => $totalOut, 's' => XlsxWriter::STYLE_TOTAL],
        ];

        $writer->addSheet('Detail Transaksi', $txRows, $this->tableOptions(
            ['A' => 8,'B' =>  ​18,'C' =>  ​28,'D' =>  ​22,'E' =>  ​16,'F' =>  ​30,'G' =>  ​14,'H' =>  ​14],
            $dataCount,
            'H'
        ));

        $accountRows = [[
            $this->headerCell('No.'), $this->headerCell('Nama Akun'), $this->headerCell('Jenis'), $this->headerCell('Saldo'),
        ]];

        foreach ($detail['accounts'] as $i => $acc) {
            $accountRows[] = [
                ['v' => $i + 1], ['v' => $acc['name']], ['v' => $acc['type']], ['v' => $acc['balance']],
            ];
        }

        $accountRows[] = [
            ['v' => 'TOTAL', 's' => XlsxWriter::STYLE_TOTAL], '', '',
            ['v' => array_sum(array_column($detail['accounts'], 'balance')), 's' => XlsxWriter::STYLE_TOTAL],
        ];

        $writer->addSheet('Saldo Akun', $accountRows, $this->tableOptions(
            ['A' => 8,'B' =>  ​30,'C' =>  ​16,'D' =>  ​16],
            count($detail['accounts']),
            'D'
        ));
    }

    /** ------------------------------------------------------------------ *
     * 3. Umur Piutang & Hutang (AR/AP Aging)
     * ------------------------------------------------------------------ */

    private function buildAging(XlsxWriter $writer, Business $business): void
    {
        $data = $this->financialReportService->getAgingSummary($business);

        $rows = [
            [['v' => 'LAPORAN UMUR PIUTANG & HUTANG (AR/AP)', 's' => XlsxWriter::STYLE_TITLE]],
            [['v' => 'Cut-off: ' . Carbon::today()->format('d M Y') . '   Dicetak: ' . date('d/m/Y H:i')]],

            [['v' => 'PIUTANG USAHA (AR)', 's' => XlsxWriter::STYLE_SECTION]],
        ];

        foreach ($data['ar']['buckets'] as $key => $amount) {
            $rows[] = [$this->bucketLabel($key, 'ar'), $amount];
        }

        $rows[] = [['v' => 'TOTAL PIUTANG', 's' => XlsxWriter::STYLE_TOTAL], ['v' => $data['ar']['total_balance'], 's' => XlsxWriter::STYLE_TOTAL]];
        $rows[] = [];
        $rows[] = [['v' => 'HUTANG USAHA (AP)', 's' => XlsxWriter::STYLE_SECTION]];

        foreach ($data['ap']['buckets'] as $key => $amount) {
            $rows[] = [$this->bucketLabel($key, 'ap'), $amount];
        }

        $rows[] = [['v' => 'TOTAL HUTANG', 's' => XlsxWriter::STYLE_TOTAL], ['v' => $data['ap']['total_balance'], 's' => XlsxWriter::STYLE_TOTAL]];

        $writer->addSheet('Ringkasan Aging', $rows, [
            'widths' => ['A' =>  ​38, 'B' => 18],
            'merge' => ['A1:C1', 'A2:C2'],
        ]);

        $writer->addSheet('Piutang AR', $this->arApRows($data['ar']['details'], true), $this->tableOptions(
            ['A' => 8,'B' =>  ​22,'C' =>  ​26,'D' =>  ​16,'E' =>  ​16,'F' =>  ​14,'G' =>  ​16,'H' =>  ​16,'I' =>  ​26],
            count($data['ar']['details']),
            'I'
        ));
        $writer->addSheet('Hutang AP', $this->arApRows($data['ap']['details'], false), $this->tableOptions(
            ['A' => 8,'B' =>  ​22,'C' =>  ​26,'D' =>  ​16,'E' =>  ​16,'F' =>  ​14,'G' =>  ​16,'H' =>  ​16,'I' =>  ​26],
            count($data['ap']['details']),
            'I'
        ));
    }

    private function arApRows(array $details, bool $isAr): array
    {
        $rows = [[
            $this->headerCell('No.'),
            $this->headerCell($isAr ? 'No. Invoice' : 'No. Tagihan'),
            $this->headerCell($isAr ? 'Pelanggan' : 'Supplier'),
            $this->headerCell('Tgl Tagihan'),
            $this->headerCell('Jatuh Tempo'),
            $this->headerCell('Hari Terlewat'),
            $this->headerCell('Nilai'),
            $this->headerCell('Sisa'),
            $this->headerCell('Status Umur'),
        ]];

        foreach ($details as $i => $d) {
            $rows[] = [
                ['v' => $i + 1], ['v' => $d['invoice_number']], ['v' => $isAr ? $d['customer_name'] : $d['supplier_name']],
                ['v' => $d['invoice_date']], ['v' => $d['due_date']], ['v' => $d['days_overdue']],
                ['v' => $d['total_amount']], ['v' => $d['balance_due']], ['v' => $d['bucket']],
            ];
        }

        $rows[] = [
            ['v' => 'TOTAL', 's' => XlsxWriter::STYLE_TOTAL], '', '', '', '', '',
            ['v' => array_sum(array_column($details, 'total_amount')), 's' => XlsxWriter::STYLE_TOTAL],
            ['v' => array_sum(array_column($details, 'balance_due')), 's' => XlsxWriter::STYLE_TOTAL],
        ];

        return $rows;


    }

    private function bucketLabel(string $key, string $prefix): string
    {
        $labels = [
            'ar' => ['current' => 'Belum Jatuh Tempo', '1_30' => '1 -  ​30 Hari', '31_60' => '31 -  ​60 Hari', '61_90' => '61 -  ​90 Hari', 'over_90' => '> ​90 Hari (Macet)'],
            'ap' => ['current' => 'Belum Jatuh Tempo', '1_30' => '1 -  ​30 Hari', '31_60' => '31 -  ​60 Hari', 'over_60' => '> ​60 Hari'],
        ];

        return $labels[$prefix][$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /** ------------------------------------------------------------------ *
     * 4. Valuasi Stok & Perputaran
     * ------------------------------------------------------------------ */

    private function buildStock(XlsxWriter $writer, Business $business): void
    {
        $data = $this->financialReportService->getStockValuationAndTurnover($business);

        $rows = [
            [['v' => 'LAPORAN VALUASI PERSEDIAAN & PERPUTARAN STOK', 's' => XlsxWriter::STYLE_TITLE]],
            [['v' => 'Bisnis: ' . $business->name . '   Dicetak: ' . date('d/m/Y H:i')]],
            ['TOTAL NILAI VALUASI', $data['summary']['total_valuation']],
            ['TOTAL UNIT FISIK', $data['summary']['total_physical_units']],
            ['TOTAL PRODUK', $data['summary']['total_products']],
            [],
            [['v' => 'RINCIAN PER KATEGORI', 's' => XlsxWriter::STYLE_SECTION]],
        ];

        foreach ($data['summary']['category_breakdown'] as $cat => $value) {
            $rows[] = [$cat, $value];
        }

        $writer->addSheet('Ringkasan Stok', $rows, [
            'widths' => ['A' =>  ​40, 'B' => 18],
            'merge' => ['A1:C1', 'A2:C2'],
        ]);

        $widths = ['A' => 16,'B' =>  ​30,'C' =>  ​20,'D' =>  ​16,'E' =>  ​12,'F' =>  ​16,'G' =>  ​18,'H' =>  ​16,'I' =>  ​18,'J' =>  ​22];

        $writer->addSheet('Detail Stok', $this->stockRows($data['all_items']), $this->tableOptions($widths, count($data['all_items']), 'J'));
        $writer->addSheet('Fast Moving', $this->stockRows($data['fast_moving']), $this->tableOptions($widths, count($data['fast_moving']), 'J'));
        $writer->addSheet('Dead Stock', $this->stockRows($data['dead_stock']), $this->tableOptions($widths, count($data['dead_stock']), 'J'));
    }

    private function stockRows(array $items): array
    {
        $rows = [[
            $this->headerCell('SKU'), $this->headerCell('Nama Produk'), $this->headerCell('Kategori'), $this->headerCell('Stok Saat Ini'),
            $this->headerCell('Satuan'), $this->headerCell('HPP Rata-rata'), $this->headerCell('Total Nilai Valuasi'), $this->headerCell('Terjual 30H'),
            $this->headerCell('Omset 30H'), $this->headerCell('Status Perputaran'),
        ]];

        foreach ($items as $it) {
            $rows[] = [
                ['v' => $it['sku']], ['v' => $it['name']], ['v' => $it['category']], ['v' => $it['current_stock']],
                ['v' => $it['unit']], ['v' => $it['unit_cost']], ['v' => $it['valuation']], ['v' => $it['sold_30d_qty']],
                ['v' => $it['sold_30d_rev']], ['v' => $it['velocity_label']],
            ];
        }

        if ($items !== []) {
            $rows[] = [
                ['v' => 'TOTAL', 's' => XlsxWriter::STYLE_TOTAL], '', '',
                ['v' => array_sum(array_column($items, 'current_stock')), 's' => XlsxWriter::STYLE_TOTAL], '',
                ['v' => array_sum(array_column($items, 'valuation')), 's' => XlsxWriter::STYLE_TOTAL], '', '', '',
            ];
        }

        return $rows;

    }