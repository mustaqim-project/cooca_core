<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Report\ReportingService;
use App\Http\Controllers\Controller;
use App\Support\Context;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ReportWebController extends Controller
{
    public function __construct(private readonly ReportingService $reportingService = new ReportingService) {}

    /**
     * Show reports and analytical charts page.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $hppReport = $this->reportingService->hppPerProduct();
        $costBreakdown = $this->reportingService->costBreakdownSummary();

        return view('app.reports.index', compact('business', 'hppReport', 'costBreakdown'));
    }

    /**
     * Export complete costing and pricing report to CSV / Excel spreadsheet.
     */
    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
     {
         $business = Context::requireBusiness();
         $reportData = $this->reportingService->hppPerProduct();

         $filename = 'Laporan_HPP_Laba_Margin_' . \Illuminate\Support\Str::slug($business->name) . '_' . date('Y-m-d_His') . '.csv';

         $headers = [
             'Content-Type' => 'text/csv; charset=UTF-8',
             'Content-Disposition' => "attachment; filename=\"{$filename}\"",
             'Pragma' => 'no-cache',
             'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
             'Expires' => '0',
         ];

         $callback = function () use ($reportData, $business): void {
             $file = fopen('php://output', 'w');
             
             // Add UTF-8 BOM for Microsoft Excel proper character rendering
             fputs($file, "\xEF\xBB\xBF");

             // Header metadata
             fputcsv($file, ['LAPORAN LENGKAP BIAYA POKOK PRODUKSI (HPP), MODAL, HARGA JUAL & MARGIN LABA']);
             fputcsv($file, ['Nama Bisnis', $business->name]);
             fputcsv($file, ['Mata Uang', $business->currency_code . ' (' . $business->currency_symbol . ')']);
             fputcsv($file, ['Tanggal Export', date('d F Y H:i:s')]);
             fputcsv($file, []); // Blank line

             // Table Columns
             fputcsv($file, [
                 'No',
                 'SKU Produk',
                 'Nama Produk',
                 'Kategori',
                 'Satuan Output',
                 'Metode HPP',
                 'Biaya Bahan Baku (Modal Material)',
                 'Biaya Tenaga Kerja (Labor)',
                 'Biaya Mesin & Utilitas',
                 'Biaya Overhead (BOP)',
                 'Total HPP per Unit (Modal Bersih)',
                 'Rekomendasi Harga Jual',
                 'Laba Kotor per Unit (Gross Profit)',
                 'Target Margin (%)',
                 'Markup (%)',
                 'Versi Kalkulasi',
             ]);

             $no = 1;
             foreach ($reportData as $row) {
                 fputcsv($file, [
                     $no++,
                     $row['sku'] ?? '-',
                     $row['product_name'],
                     $row['category'],
                     $row['output_unit'],
                     strtoupper((string) $row['costing_method']),
                     round((float) ($row['material_cost'] ?? 0)),
                     round((float) ($row['labor_cost'] ?? 0)),
                     round((float) ($row['machine_cost'] ?? 0)),
                     round((float) ($row['overhead_cost'] ?? 0)),
                     round((float) ($row['hpp_per_unit'] ?? 0)),
                     round((float) ($row['recommended_price'] ?? 0)),
                     round((float) ($row['gross_profit'] ?? 0)),
                     $row['margin_percentage'] . '%',
                     $row['markup_percentage'] . '%',
                     $row['version_label'] ?? 'v1.0',
                 ]);
             }

             fclose($file);
         };

         return response()->stream($callback, 200, $headers);
     }
}
