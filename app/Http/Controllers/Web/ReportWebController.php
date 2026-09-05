<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Report\FinancialReportService;
use App\Domain\Report\ReportingService;
use App\Http\Controllers\Controller;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportWebController extends Controller
{
    public function __construct(
        private readonly ReportingService $reportingService = new ReportingService,
        private readonly FinancialReportService $financialReportService = new FinancialReportService
    ) {}

    /**
     * Tampilkan Suite Laporan & Analitik Finansial Komprehensif.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        // Filter Rentang Waktu
        $preset = $request->query('preset', 'this_month');
        $startDate = null;
        $endDate = null;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->query('start_date'));
            $endDate   = Carbon::parse($request->query('end_date'));
            $preset    = 'custom';
        } else {
            switch ($preset) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate   = Carbon::today();
                    break;
                case '7days':
                    $startDate = Carbon::today()->subDays(6);
                    $endDate   = Carbon::today();
                    break;
                case 'this_year':
                    $startDate = Carbon::today()->startOfYear();
                    $endDate   = Carbon::today()->endOfYear();
                    break;
                case 'this_month':
                default:
                    $startDate = Carbon::today()->startOfMonth();
                    $endDate   = Carbon::today()->endOfMonth();
                    $preset    = 'this_month';
                    break;
            }
        }

        $activeTab = $request->query('tab', 'income_statement');

        // 1. Laba Rugi
        $incomeStatement = $this->financialReportService->getIncomeStatement($business, $startDate, $endDate);

        // 2. Arus Kas
        $cashFlow = $this->financialReportService->getCashFlowStatement($business, $startDate, $endDate);

        // 3. AR / AP Aging
        $agingSummary = $this->financialReportService->getAgingSummary($business);

        // 4. Valuasi & Perputaran Stok
        $stockValuation = $this->financialReportService->getStockValuationAndTurnover($business);

        // 5. Struktur HPP & Biaya Pokok
        $hppReport = $this->reportingService->hppPerProduct();
        $costBreakdown = $this->reportingService->costBreakdownSummary();

        return view('app.reports.index', compact(
            'business',
            'preset',
            'startDate',
            'endDate',
            'activeTab',
            'incomeStatement',
            'cashFlow',
            'agingSummary',
            'stockValuation',
            'hppReport',
            'costBreakdown'
        ));
    }

    /**
     * Export Laporan ke Excel / CSV berdasarkan tab/jenis yang diminta.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $business = Context::requireBusiness();
        $type = $request->query('type', 'income_statement');

        $startDate = $request->filled('start_date') ? Carbon::parse($request->query('start_date')) : Carbon::today()->startOfMonth();
        $endDate   = $request->filled('end_date') ? Carbon::parse($request->query('end_date')) : Carbon::today()->endOfMonth();

        $filename = 'Laporan_' . Str::studly($type) . '_' . Str::slug($business->name) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($type, $business, $startDate, $endDate): void {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            if ($type === 'income_statement') {
                $this->exportIncomeStatementCsv($file, $business, $startDate, $endDate);
            } elseif ($type === 'cash_flow') {
                $this->exportCashFlowCsv($file, $business, $startDate, $endDate);
            } elseif ($type === 'aging') {
                $this->exportAgingCsv($file, $business);
            } elseif ($type === 'stock_valuation') {
                $this->exportStockValuationCsv($file, $business);
            } else {
                $this->exportHppCsv($file, $business);
            }

            fclose($file);
        }, 200, $headers);
    }

    private function exportIncomeStatementCsv($file, $business, $startDate, $endDate): void
    {
        $data = $this->financialReportService->getIncomeStatement($business, $startDate, $endDate);

        fputcsv($file, ['LAPORAN LABA RUGI KOMPREHENSIF (INCOME STATEMENT)']);
        fputcsv($file, ['Bisnis', $business->name]);
        fputcsv($file, ['Periode', $data['period']['label']]);
        fputcsv($file, ['Tanggal Cetak', date('d/m/Y H:i:s')]);
        fputcsv($file, []);

        fputcsv($file, ['KOMPONEN KEUANGAN', 'NOMINAL (' . $business->currency_symbol . ')']);
        fputcsv($file, ['PENDAPATAN / PENJUALAN', '']);
        fputcsv($file, ['  Penjualan Kasir POS (Kotor)', round($data['revenues']['pos_gross_sales'])]);
        fputcsv($file, ['  Penjualan Faktur Invoice (Kotor)', round($data['revenues']['invoice_gross_sales'])]);
        fputcsv($file, ['  Total Penjualan Kotor', round($data['revenues']['total_gross_sales'])]);
        fputcsv($file, ['  Potongan Diskon & Voucher', round($data['revenues']['total_discounts'])]);
        fputcsv($file, ['  Retur Penjualan (Pengurang)', round($data['revenues']['sales_returns'])]);
        fputcsv($file, ['TOTAL PENDAPATAN BERSIH', round($data['revenues']['net_sales'])]);
        fputcsv($file, []);
        fputcsv($file, ['PAJAK YANG DIPUNGUT (PPN / TAX)', '']);
        fputcsv($file, ['  Pajak PPN Transaksi Kasir POS', round($data['revenues']['pos_tax'])]);
        fputcsv($file, ['  Pajak PPN Faktur Invoice', round($data['revenues']['invoice_tax'])]);
        fputcsv($file, ['TOTAL PAJAK DIPUNGUT', round($data['revenues']['total_tax'])]);
        fputcsv($file, []);

        fputcsv($file, ['HARGA POKOK PENJUALAN (HPP / COGS)', '']);
        fputcsv($file, ['  HPP Penjualan Kasir POS', round($data['cogs']['pos_cogs'])]);
        fputcsv($file, ['  HPP Penjualan Faktur Invoice', round($data['cogs']['invoice_cogs'])]);
        fputcsv($file, ['  Pemulihan HPP Retur (Pengurang)', round($data['cogs']['returns_cogs_recovery'])]);
        fputcsv($file, ['TOTAL HPP BARANG TERJUAL', round($data['cogs']['total_cogs'])]);
        fputcsv($file, []);

        fputcsv($file, ['LABA KOTOR (GROSS PROFIT)', round($data['gross_profit']['amount'])]);
        fputcsv($file, ['MARGIN LABA KOTOR (%)', $data['gross_profit']['margin'] . '%']);
        fputcsv($file, []);

        fputcsv($file, ['BEBAN OPERASIONAL (EXPENSES)', '']);
        foreach ($data['expenses']['by_category'] as $cat => $amount) {
            fputcsv($file, ['  Beban: ' . $cat, round($amount)]);
        }
        fputcsv($file, ['TOTAL BEBAN OPERASIONAL', round($data['expenses']['total'])]);
        fputcsv($file, []);

        fputcsv($file, ['LABA BERSIH OPERASIONAL (NET PROFIT)', round($data['net_profit']['amount'])]);
        fputcsv($file, ['MARGIN LABA BERSIH (%)', $data['net_profit']['margin'] . '%']);
    }

    private function exportCashFlowCsv($file, $business, $startDate, $endDate): void
    {
        $data = $this->financialReportService->getCashFlowStatement($business, $startDate, $endDate);

        fputcsv($file, ['LAPORAN ARUS KAS (CASH FLOW STATEMENT)']);
        fputcsv($file, ['Bisnis', $business->name]);
        fputcsv($file, ['Periode', $data['period']['label']]);
        fputcsv($file, []);

        fputcsv($file, ['ARUS KAS MASUK (INFLOWS)', 'NOMINAL (' . $business->currency_symbol . ')']);
        fputcsv($file, ['  Penerimaan Kasir POS', round($data['inflows']['pos_payments'])]);
        fputcsv($file, ['  Pelunasan Piutang Invoice Pelanggan', round($data['inflows']['invoice_payments'])]);
        fputcsv($file, ['  Pemasukan Kas Langsung / Non-Penjualan', round($data['inflows']['direct_cash_in'])]);
        fputcsv($file, ['TOTAL ARUS KAS MASUK', round($data['inflows']['total'])]);
        fputcsv($file, []);

        fputcsv($file, ['ARUS KAS KELUAR (OUTFLOWS)', 'NOMINAL (' . $business->currency_symbol . ')']);
        fputcsv($file, ['  Pelunasan Hutang Supplier (AP)', round($data['outflows']['supplier_payments'])]);
        fputcsv($file, ['  Pengeluaran Beban Operasional', round($data['outflows']['expenses'])]);
        fputcsv($file, ['  Pengembalian Dana Kas Retur (Refund)', round($data['outflows']['cash_refunds'])]);
        fputcsv($file, ['  Pengeluaran Kas Langsung', round($data['outflows']['direct_cash_out'])]);
        fputcsv($file, ['TOTAL ARUS KAS KELUAR', round($data['outflows']['total'])]);
        fputcsv($file, []);

        fputcsv($file, ['ARUS KAS BERSIH PERIODE INI (NET CASH FLOW)', round($data['net_cash_flow'])]);
        fputcsv($file, ['TOTAL SALDO KAS & BANK TERKINI', round($data['accounts']['total_balance'])]);
    }

    private function exportAgingCsv($file, $business): void
    {
        $data = $this->financialReportService->getAgingSummary($business);

        fputcsv($file, ['LAPORAN ANALISIS UMUR PIUTANG (AR) & HUTANG (AP)']);
        fputcsv($file, ['Bisnis', $business->name]);
        fputcsv($file, ['Tanggal Cut-off', date('d/m/Y')]);
        fputcsv($file, []);

        fputcsv($file, ['--- 1. DAFTAR PIUTANG PELANGGAN (ACCOUNTS RECEIVABLE) ---']);
        fputcsv($file, ['No. Invoice', 'Pelanggan', 'Tgl Faktur', 'Jatuh Tempo', 'Hari Terlewat', 'Nilai Faktur', 'Sisa Piutang', 'Status Umur']);
        foreach ($data['ar']['details'] as $ar) {
            fputcsv($file, [
                $ar['invoice_number'],
                $ar['customer_name'],
                $ar['invoice_date'],
                $ar['due_date'],
                $ar['days_overdue'] . ' hari',
                round($ar['total_amount']),
                round($ar['balance_due']),
                $ar['bucket'],
            ]);
        }
        fputcsv($file, ['TOTAL PIUTANG USAHA BEREDAR', '', '', '', '', '', round($data['ar']['total_balance']), '']);
        fputcsv($file, []);

        fputcsv($file, ['--- 2. DAFTAR HUTANG SUPPLIER (ACCOUNTS PAYABLE) ---']);
        fputcsv($file, ['No. Tagihan Vendor', 'Supplier', 'Tgl Tagihan', 'Jatuh Tempo', 'Hari Terlewat', 'Nilai Tagihan', 'Sisa Hutang', 'Status Umur']);
        foreach ($data['ap']['details'] as $ap) {
            fputcsv($file, [
                $ap['invoice_number'],
                $ap['supplier_name'],
                $ap['invoice_date'],
                $ap['due_date'],
                $ap['days_overdue'] . ' hari',
                round($ap['total_amount']),
                round($ap['balance_due']),
                $ap['bucket'],
            ]);
        }
        fputcsv($file, ['TOTAL HUTANG USAHA BEREDAR', '', '', '', '', '', round($data['ap']['total_balance']), '']);
    }

    private function exportStockValuationCsv($file, $business): void
    {
        $data = $this->financialReportService->getStockValuationAndTurnover($business);

        fputcsv($file, ['LAPORAN VALUASI PERSEDIAAN & PERPUTARAN STOK']);
        fputcsv($file, ['Bisnis', $business->name]);
        fputcsv($file, ['Tanggal Cetak', date('d/m/Y')]);
        fputcsv($file, []);

        fputcsv($file, ['SKU', 'Nama Produk', 'Kategori', 'Stok Saat Ini', 'Satuan', 'HPP Rata-rata (WAC)', 'Total Nilai Valuasi', 'Terjual 30 Hari', 'Omset 30 Hari', 'Status Perputaran']);
        foreach ($data['all_items'] as $item) {
            fputcsv($file, [
                $item['sku'],
                $item['name'],
                $item['category'],
                $item['current_stock'],
                $item['unit'],
                round($item['unit_cost']),
                round($item['valuation']),
                $item['sold_30d_qty'],
                round($item['sold_30d_rev']),
                $item['velocity_label'],
            ]);
        }
        fputcsv($file, []);
        fputcsv($file, ['TOTAL VALUASI STOK KESELURUHAN', round($data['summary']['total_valuation'])]);
        fputcsv($file, ['TOTAL UNIT FISIK', $data['summary']['total_physical_units']]);
    }

    private function exportHppCsv($file, $business): void
    {
        $reportData = $this->reportingService->hppPerProduct();

        fputcsv($file, ['LAPORAN BIAYA POKOK PRODUKSI (HPP) & MARGIN PRODUK']);
        fputcsv($file, ['Bisnis', $business->name]);
        fputcsv($file, ['Tanggal Cetak', date('d/m/Y')]);
        fputcsv($file, []);

        fputcsv($file, ['SKU', 'Nama Produk', 'Kategori', 'Satuan', 'Biaya Bahan', 'Labor & Mesin', 'Overhead', 'Total HPP Modal', 'Harga Jual Standar', 'Laba Kotor / Unit', 'Margin %']);
        foreach ($reportData as $row) {
            fputcsv($file, [
                $row['sku'],
                $row['product_name'],
                $row['category'],
                $row['output_unit'],
                round((float) ($row['material_cost'] ?? 0)),
                round((float) ($row['labor_cost'] ?? 0) + (float) ($row['machine_cost'] ?? 0)),
                round((float) ($row['overhead_cost'] ?? 0)),
                round((float) ($row['hpp_per_unit'] ?? 0)),
                round((float) ($row['recommended_price'] ?? 0)),
                round((float) ($row['gross_profit'] ?? 0)),
                $row['margin_percentage'] . '%',
            ]);
        }
    }
}
