<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Domain\Report\Pos\PosReportingService;
use App\Domain\Report\SalesReportService;
use App\Exports\PosReportExport;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PosReportWebController extends Controller
{
    public function __construct(
        private readonly PosReportingService $reportingService = new PosReportingService,
        private readonly SalesReportService $salesReport = new SalesReportService
    ) {}

    /**
     * Display POS Analytics Dashboard.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $filter = PosReportFilterDTO::fromRequest($request, $business->id);

        $startDate = $filter->startDate;
        $endDate = $filter->endDate;

        // 1. KPI Summary (Single Source of Truth)
        $kpi = $this->reportingService->getKpiSummary($filter);

        $totalRevenue = $kpi->netSales;
        $totalSubtotal = $kpi->subtotal;
        $totalDiscount = $kpi->orderDiscount;
        $totalVoucherDiscount = $kpi->voucherDiscount;
        $totalPointsDiscount = $kpi->pointsDiscount;
        $totalTax = $kpi->taxAmount;
        $totalServiceCharge = $kpi->serviceChargeAmount;
        $totalRounding = $kpi->roundingAmount;
        $totalHpp = $kpi->totalHpp;
        $totalGrossProfit = $kpi->grossProfit;
        $grossMarginPercent = $kpi->grossMarginPercent;
        $ordersCount = $kpi->totalOrders;
        $averageOrderValue = $kpi->averageOrderValue;
        $goodsRevenue = $kpi->goodsRevenue;
        $goodsQty = $kpi->goodsQuantity;
        $servicesRevenue = $kpi->servicesRevenue;
        $servicesQty = $kpi->servicesQuantity;
        $todayRevenue = $kpi->todayRevenue;
        $todayOrders = $kpi->todayOrders;
        $averageSellingPrice = $kpi->averageSellingPrice;
        $averageCostPrice = $kpi->averageCostPrice;

        // 2. Snapshot average prices (Legacy compatibility)
        $snapshotReport = $this->salesReport->summary($business->id, $startDate, $endDate, $filter->locationId);
        $snapshotTotalQty = (float) $snapshotReport['summary']['total_quantity'];
        $snapshotTotalSales = (float) $snapshotReport['summary']['total_sales'];
        $snapshotTotalModal = (float) $snapshotReport['summary']['total_modal'];
        $snapshotGrossProfit = (float) $snapshotReport['summary']['total_gross_profit'];
        $snapshotMarginPercent = (float) $snapshotReport['summary']['margin_percentage'];
        $productAveragePrices = $snapshotReport['by_product'];

        // 3. Sub-aggregations via PosReportingService
        $dailyTrend = $this->reportingService->getDailySalesTrend($filter);
        $hourlyData = $this->reportingService->getHourlyHeatmap($filter);
        $paymentMethods = $this->reportingService->getPaymentMethodBreakdown($filter);
        $topProducts = $this->reportingService->getProductPerformance($filter, 10);
        $cashierPerformance = $this->reportingService->getCashierPerformance($filter);

        return view('app.pos.reports', compact(
            'business',
            'startDate',
            'endDate',
            'totalRevenue',
            'goodsRevenue',
            'goodsQty',
            'servicesRevenue',
            'servicesQty',
            'totalSubtotal',
            'totalDiscount',
            'totalVoucherDiscount',
            'totalPointsDiscount',
            'totalTax',
            'totalServiceCharge',
            'totalRounding',
            'totalHpp',
            'totalGrossProfit',
            'grossMarginPercent',
            'ordersCount',
            'averageOrderValue',
            'todayRevenue',
            'todayOrders',
            'dailyTrend',
            'hourlyData',
            'paymentMethods',
            'topProducts',
            'cashierPerformance',
            'averageSellingPrice',
            'averageCostPrice',
            'snapshotTotalQty',
            'snapshotTotalSales',
            'snapshotTotalModal',
            'snapshotGrossProfit',
            'snapshotMarginPercent',
            'productAveragePrices'
        ));
    }

    /**
     * Export POS Sales ke file Excel (XLSX) multi-sheet standar COOCA.
     *
     * Menghasilkan file Excel XLSX profesional 2-Bagian:
     *   - Sheet 1: Ringkasan Eksekutif & Bento KPI Cards
     *   - Sheet 2: Rincian Transaksi Transaksional (Transaction Ledger)
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $business = Context::requireBusiness();

        // Filter tanggal default mengikuti ringkasan di halaman (30 hari terakhir).
        $startDate = $request->filled('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : Carbon::today()->subDays(29)->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : Carbon::today()->endOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $orders = PosOrder::where('business_id', $business->id)
            ->whereIn('status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND])
            ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['customer', 'user', 'location', 'payments', 'items.product'])
            ->orderBy('order_date')
            ->orderBy('created_at')
            ->get();

        if ($request->get('format') === 'csv') {
            $filename = 'laporan-penjualan-pos-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.csv';
            $headers = [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            return response()->stream(function () use ($orders, $business, $startDate, $endDate): void {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                $this->exportPosDetailCsv($file, $orders, $business, $startDate, $endDate);
                fclose($file);
            }, 200, $headers);
        }

        $exporter = new PosReportExport();
        return $exporter->download($business, $orders, $startDate, $endDate);
    }

    /**
     * Menulis seluruh bagian laporan detail ke dalam file CSV.
     *
     * @param resource $file
     * @param \Illuminate\Support\Collection<int, PosOrder> $orders
     */
    private function exportPosDetailCsv($file, $orders, Business $business, Carbon $startDate, Carbon $endDate): void
    {
        $orders = $orders->values();

        // KPI periode (angka sama dengan ringkasan halaman)
        $totalRevenue         = (float) $orders->sum('total_amount');
        $totalSubtotal        = (float) $orders->sum('subtotal');
        $totalDiscount        = (float) $orders->sum('discount_amount');
        $totalVoucherDiscount = (float) $orders->sum('voucher_discount_amount');
        $totalPointsDiscount  = (float) $orders->sum('points_discount_amount');
        $totalTax             = (float) $orders->sum('tax_amount');
        $totalServiceCharge   = (float) $orders->sum('service_charge_amount');
        $totalRounding        = (float) $orders->sum('rounding_amount');
        $totalHpp             = (float) $orders->sum('total_hpp_cost');
        $totalGrossProfit     = (float) $orders->sum('total_gross_profit');
        $ordersCount          = $orders->count();
        $averageOrderValue    = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0.0;
        $grossMarginPercent   = $totalRevenue > 0 ? ($totalGrossProfit / $totalRevenue) * 100 : 0.0;

        $periodLabel = $startDate->format('d/m/Y') . ' s/d ' . $endDate->format('d/m/Y');

        fputcsv($file, ['LAPORAN PENJUALAN KASIR POS - DETAIL TRANSAKSI, ITEM & PEMBAYARAN']);
        fputcsv($file, ['Bisnis', $business->name]);
        fputcsv($file, ['Periode', $periodLabel]);
        fputcsv($file, ['Tanggal Cetak', date('d/m/Y H:i:s')]);
        fputcsv($file, []);

        fputcsv($file, ['RINGKASAN KINERJA', 'NOMINAL (' . $business->currency_symbol . ')']);
        fputcsv($file, ['Jumlah Transaksi', $ordersCount]);
        fputcsv($file, ['Subtotal Penjualan', round($totalSubtotal, 2)]);
        fputcsv($file, ['Total Diskon Order', round($totalDiscount, 2)]);
        fputcsv($file, ['Total Diskon Voucher', round($totalVoucherDiscount, 2)]);
        fputcsv($file, ['Total Diskon Poin', round($totalPointsDiscount, 2)]);
        fputcsv($file, ['Total Pajak / PPN', round($totalTax, 2)]);
        fputcsv($file, ['Total Service Charge', round($totalServiceCharge, 2)]);
        fputcsv($file, ['Total Pembulatan (Rounding)', round($totalRounding, 2)]);
        fputcsv($file, ['Total Penjualan (Grand Total)', round($totalRevenue, 2)]);
        fputcsv($file, ['Rata-rata Nilai Transaksi (AOV)', round($averageOrderValue, 2)]);
        fputcsv($file, ['Total HPP / Modal', round($totalHpp, 2)]);
        fputcsv($file, ['Total Laba Kotor', round($totalGrossProfit, 2)]);
        fputcsv($file, ['Margin Laba Kotor (%)', round($grossMarginPercent, 2) . '%']);
        fputcsv($file, []);
        // 1. Rincian Transaksi per Order
        fputcsv($file, ['--- 1. RINCIAN TRANSAKSI (PER ORDER) ---']);
        fputcsv($file, [
            'No. Order', 'Tanggal', 'Jam', 'Outlet', 'Kasir', 'Pelanggan', 'Tipe Pelanggan', 'Tipe Order',
            'Meja / Referensi', 'Jumlah Item', 'Subtotal', 'Tipe Diskon', 'Nilai Diskon', 'Diskon Order',
            'Kode Voucher', 'Diskon Voucher', 'Diskon Poin', 'Pajak (%)', 'Pajak',
            'Service Charge (%)', 'Service Charge', 'Pembulatan', 'Total Bayar', 'Dibayar', 'Kembalian',
            'HPP / Modal', 'Laba Kotor', 'Margin (%)', 'Metode Pembayaran', 'Status',
        ]);
        foreach ($orders as $o) {
            $payMethods = $o->payments->map(fn ($p) => $this->paymentMethodLabel($p->payment_method) . ': ' . number_format((float) $p->amount, 2, ',', '.'))->implode(' | ');
            $margin = $o->total_amount > 0 ? round(($o->total_gross_profit / $o->total_amount) * 100, 2) : 0;
            $custName = $o->customer?->name ?: ($o->customer_name_guest ?: 'Umum');
            $custType = $o->customer ? 'Member' : 'Guest / Umum';

            fputcsv($file, [
                $o->order_number,
                $o->order_date?->format('Y-m-d') ?? $o->created_at?->format('Y-m-d') ?? '-',
                $o->created_at?->format('H:i:s') ?? '-',
                $o->location->name ?? '-',
                $o->user->name ?? '-',
                $custName,
                $custType,
                strtoupper(str_replace('_', ' ', (string) $o->order_type)),
                $o->table_or_reference ?? '-',
                (int) round($o->items->sum('quantity')),
                round((float) $o->subtotal, 2),
                $this->discountTypeLabel($o->discount_type, (float) $o->discount_value, (float) $o->discount_amount),
                round((float) $o->discount_value, 2),
                round((float) $o->discount_amount, 2),
                $o->voucher_code ?: ($o->voucher_discount_amount > 0 ? 'Voucher' : '-'),
                round((float) $o->voucher_discount_amount, 2),
                round((float) $o->points_discount_amount, 2),
                round((float) $o->tax_percentage, 2),
                round((float) $o->tax_amount, 2),
                round((float) $o->service_charge_percentage, 2),
                round((float) $o->service_charge_amount, 2),
                round((float) $o->rounding_amount, 2),
                round((float) $o->total_amount, 2),
                round((float) $o->paid_amount, 2),
                round((float) $o->change_amount, 2),
                round((float) $o->total_hpp_cost, 2),
                round((float) $o->total_gross_profit, 2),
                $margin . '%',
                $payMethods ?: '-',
                strtoupper(str_replace('_', ' ', (string) $o->status)),
            ]);
        }
        fputcsv($file, ['TOTAL TRANSAKSI', $ordersCount]);
        fputcsv($file, []);

        // 2. Detail Item per Transaksi
        fputcsv($file, ['--- 2. DETAIL ITEM PER TRANSAKSI ---']);
        fputcsv($file, [
            'No. Order', 'Tanggal', 'Outlet', 'Kasir', 'Pelanggan', 'Kode Produk / SKU', 'Nama Produk',
            'Qty', 'Harga Satuan', 'Diskon Item', 'Subtotal Item', 'Total Item', 'HPP / Unit', 'Total HPP Item', 'Laba Item',
        ]);
        foreach ($orders as $o) {
            $custName = $o->customer?->name ?: ($o->customer_name_guest ?: 'Umum');
            foreach ($o->items as $it) {
                $totalHppItem = (float) $it->total_hpp > 0 ? (float) $it->total_hpp : ((float) $it->quantity * (float) $it->unit_cost_hpp);
                $profitItem = (float) $it->total_price - $totalHppItem;
                $sku = $it->product_code ?: ($it->product?->code ?: ($it->product?->sku ?: '-'));

                fputcsv($file, [
                    $o->order_number,
                    $o->order_date?->format('Y-m-d') ?? $o->created_at?->format('Y-m-d') ?? '-',
                    $o->location->name ?? '-',
                    $o->user->name ?? '-',
                    $custName,
                    $sku,
                    $it->product_name,
                    $this->formatQty($it->quantity),
                    round((float) $it->unit_price, 2),
                    round((float) $it->discount_amount, 2),
                    round((float) $it->subtotal, 2),
                    round((float) $it->total_price, 2),
                    round((float) $it->unit_cost_hpp, 2),
                    round($totalHppItem, 2),
                    round($profitItem, 2),
                ]);
            }
        }
        fputcsv($file, []);
        // 3. Rincian Pembayaran per Metode
        fputcsv($file, ['--- 3. RINCIAN PEMBAYARAN (PER METODE) ---']);
        fputcsv($file, ['No. Order', 'Tanggal', 'Metode Pembayaran', 'Nominal', 'Fee', 'Net', 'Status', 'No. Referensi', 'Keterangan']);
        foreach ($orders as $o) {
            foreach ($o->payments as $p) {
                $net = $p->net_amount !== null ? (float) $p->net_amount : (float) $p->amount;

                fputcsv($file, [
                    $o->order_number,
                    $o->order_date?->format('Y-m-d') ?? $o->created_at?->format('Y-m-d') ?? '-',
                    $this->paymentMethodLabel($p->payment_method),
                    round((float) $p->amount, 2),
                    round((float) $p->fee_amount, 2),
                    round($net, 2),
                    strtoupper(str_replace('_', ' ', (string) ($p->status ?? 'success'))),
                    $p->reference_number ?? '-',
                    $p->notes ?? '-',
                ]);
            }
        }
        fputcsv($file, []);

        // 4. Ringkasan Metode Pembayaran
        $paymentsFlat = $orders->flatMap(fn ($o) => $o->payments)->values();

        fputcsv($file, ['--- 4. RINGKASAN METODE PEMBAYARAN ---']);
        fputcsv($file, ['Metode Pembayaran', 'Jumlah Pembayaran', 'Total Nominal', 'Total Fee', 'Total Net']);
        foreach ($paymentsFlat->groupBy('payment_method') as $method => $payments) {
            fputcsv($file, [
                $this->paymentMethodLabel((string) $method),
                $payments->count(),
                round((float) $payments->sum('amount'), 2),
                round((float) $payments->sum(fn ($p) => (float) $p->fee_amount), 2),
                round((float) $payments->sum(fn ($p) => $p->net_amount !== null ? (float) $p->net_amount : (float) $p->amount), 2),
            ]);
        }
        fputcsv($file, [
            'TOTAL KESELURUHAN',
            $paymentsFlat->count(),
            round((float) $paymentsFlat->sum('amount'), 2),
            round((float) $paymentsFlat->sum(fn ($p) => (float) $p->fee_amount), 2),
            round((float) $paymentsFlat->sum(fn ($p) => $p->net_amount !== null ? (float) $p->net_amount : (float) $p->amount), 2),
        ]);
        fputcsv($file, []);

        // 5. Ringkasan Penjualan per Produk
        $itemsFlat = $orders->flatMap(fn ($o) => $o->items)->values();
        $productTotals = $itemsFlat->groupBy(fn ($it) => $it->product_id ?: ($it->product_code ?: $it->product_name));

        fputcsv($file, ['--- 5. RINGKASAN PENJUALAN PER PRODUK ---']);
        fputcsv($file, ['Kode Produk / SKU', 'Nama Produk', 'Qty Terjual', 'Total Penjualan', 'Total HPP', 'Laba Kotor', 'Margin (%)']);
        foreach ($productTotals as $productItems) {
            $firstItem = $productItems->first();
            $sku = $firstItem->product_code ?: ($firstItem->product?->code ?: ($firstItem->product?->sku ?: '-'));
            $qty = (float) $productItems->sum('quantity');
            $sales = (float) $productItems->sum('total_price');
            $hpp = (float) $productItems->sum(fn ($it) => (float) $it->total_hpp > 0 ? (float) $it->total_hpp : ((float) $it->quantity * (float) $it->unit_cost_hpp));
            $profit = $sales - $hpp;
            $margin = $sales > 0 ? ($profit / $sales) * 100 : 0.0;

            fputcsv($file, [
                $sku,
                $firstItem->product_name,
                $this->formatQty($qty),
                round($sales, 2),
                round($hpp, 2),
                round($profit, 2),
                round($margin, 2) . '%',
            ]);
        }

        $itemsTotalSales = (float) $itemsFlat->sum('total_price');
        $itemsTotalHpp = (float) $itemsFlat->sum(fn ($it) => (float) $it->total_hpp > 0 ? (float) $it->total_hpp : ((float) $it->quantity * (float) $it->unit_cost_hpp));
        $itemsTotalProfit = $itemsTotalSales - $itemsTotalHpp;
        $itemsTotalMargin = $itemsTotalSales > 0 ? round(($itemsTotalProfit / $itemsTotalSales) * 100, 2) : 0.0;

        fputcsv($file, [
            'TOTAL KESELURUHAN', '',
            $this->formatQty((float) $itemsFlat->sum('quantity')),
            round($itemsTotalSales, 2),
            round($itemsTotalHpp, 2),
            round($itemsTotalProfit, 2),
            $itemsTotalMargin . '%',
        ]);
        fputcsv($file, []);
        fputcsv($file, ['--- AKHIR LAPORAN ---']);
    }

    /**
     * Format label tipe diskon POS ('percentage' => Persentase, 'fixed' => Nominal).
     */
    private function discountTypeLabel(?string $type, float $discountValue = 0.0, float $discountAmount = 0.0): string
    {
        if ($discountValue <= 0 && $discountAmount <= 0) {
            return 'Tanpa Diskon';
        }
        return match (strtolower((string) $type)) {
            'percentage' => 'Persentase (%)',
            'fixed' => 'Nominal',
            '', 'none', 'null', '0' => 'Tanpa Diskon',
            default => ucfirst(str_replace('_', ' ', (string) $type)),
        };
    }

    /**
     * Format label metode pembayaran POS ke nama yang mudah dibaca.
     */
    private function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'cash' => 'TUNAI',
            'qris' => 'QRIS',
            'transfer' => 'TRANSFER BANK',
            'edc_debit' => 'EDC DEBIT',
            'edc_credit' => 'EDC KREDIT',
            'customer_credit' => 'KREDIT PELANGGAN (PIUTANG)',
            'loyalty_points' => 'POIN LOYALITAS',
            default => strtoupper(str_replace('_', ' ', $method)),
        };
    }

    /**
     * Format qty agar tidak membawa angka desimal berlebihan (8000 => 8, 1500 => 1.5).
     */
    private function formatQty(float $quantity): string
    {
        $formatted = rtrim(rtrim(number_format($quantity, 4, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }
}
