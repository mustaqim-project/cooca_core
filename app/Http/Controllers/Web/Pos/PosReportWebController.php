<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Report\SalesReportService;
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
        private readonly SalesReportService $salesReport = new SalesReportService
    ) {}

    /**
     * Display POS Analytics Dashboard.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::today()->subDays(29);
        $endDate = $request->filled('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::today();

        $baseOrdersQuery = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED);

        // Filtered range
        $rangeOrders = (clone $baseOrdersQuery)
            ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()]);

        // Key KPI metrics
        $totalRevenue = (float) (clone $rangeOrders)->sum('total_amount');
        $totalDiscount = (float) (clone $rangeOrders)->sum('discount_amount');
        $totalTax = (float) (clone $rangeOrders)->sum('tax_amount');
        $totalHpp = (float) (clone $rangeOrders)->sum('total_hpp_cost');
        $totalGrossProfit = (float) (clone $rangeOrders)->sum('total_gross_profit');
        $ordersCount = (clone $rangeOrders)->count();
        $averageOrderValue = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0.0;
        $grossMarginPercent = $totalRevenue > 0 ? ($totalGrossProfit / $totalRevenue) * 100 : 0.0;

        // ── Snapshot average prices (weighted average dari detail transaksi) ──
        $snapshotReport = $this->salesReport->summary($business->id, $startDate, $endDate);
        $averageSellingPrice = (float) $snapshotReport['summary']['average_selling_price'];
        $averageCostPrice = (float) $snapshotReport['summary']['average_cost_price'];
        $snapshotTotalQty = (float) $snapshotReport['summary']['total_quantity'];
        $snapshotTotalSales = (float) $snapshotReport['summary']['total_sales'];
        $snapshotTotalModal = (float) $snapshotReport['summary']['total_modal'];
        $snapshotGrossProfit = (float) $snapshotReport['summary']['total_gross_profit'];
        $snapshotMarginPercent = (float) $snapshotReport['summary']['margin_percentage'];
        $productAveragePrices = $snapshotReport['by_product'];

        // Today's summary
        $todayRevenue = (float) (clone $baseOrdersQuery)->whereDate('order_date', Carbon::today())->sum('total_amount');
        $todayOrders = (clone $baseOrdersQuery)->whereDate('order_date', Carbon::today())->count();

        // 1. Daily Sales Trend (Last 14 days)
        $dailyTrend = (clone $rangeOrders)
            ->selectRaw('order_date, SUM(total_amount) as revenue, SUM(total_hpp_cost) as hpp, SUM(total_gross_profit) as profit')
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->get();

        // 2. Peak Hours Analysis (Hourly Sales)
        $hourlyData = (clone $rangeOrders)
            ->selectRaw('HOUR(created_at) as order_hour, COUNT(*) as orders_count, SUM(total_amount) as total_sales')
            ->groupBy('order_hour')
            ->orderBy('order_hour')
            ->get();

        // 3. Payment Method Breakdown
        $paymentMethods = PosOrderPayment::whereHas('order', function ($q) use ($business, $startDate, $endDate) {
            $q->where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()]);
        })
            ->selectRaw('payment_method, SUM(amount) as total_amount, COUNT(*) as tx_count')
            ->groupBy('payment_method')
            ->get();

        // 4. Top 5 Selling Products
        $topProducts = PosOrderItem::whereHas('order', function ($q) use ($business, $startDate, $endDate) {
            $q->where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()]);
        })
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue, SUM(total_hpp) as total_cost')
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // 5. Sales by Cashier
        $cashierPerformance = (clone $rangeOrders)
            ->with('user')
            ->selectRaw('user_id, COUNT(*) as orders_count, SUM(total_amount) as total_sales')
            ->groupBy('user_id')
            ->get();

        return view('app.pos.reports', compact(
            'business',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalDiscount',
            'totalTax',
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
     * Export POS Sales ke file Excel-compatible CSV.
     *
     * Menghasilkan CSV ber-UTF8-BOM yang memuat:
     *   - Ringkasan KPI periode (konsisten dengan ringkasan di halaman)
     *   - 1. Rincian Transaksi per Order (tipe & nilai diskon, persentase pajak, rounding, pembayaran)
     *   - 2. Detail Item per Transaksi
     *   - 3. Rincian Pembayaran per Metode
     *   - 4. Ringkasan Metode Pembayaran
     *   - 5. Ringkasan Penjualan per Produk
     *
     * Filter tanggal diberikan lewat query param `start_date` & `end_date`
     * (sama seperti halaman laporan) sehingga export menghormati rentang
     * yang sedang dilihat user, bukan seluruh histori transaksi.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $business = Context::requireBusiness();

        // Filter tanggal default mengikuti ringkasan di halaman (30 hari terakhir).
        $startDate = $request->filled('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::today()->subDays(29);
        $endDate = $request->filled('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::today();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $orders = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['customer', 'user', 'location', 'payments', 'items'])
            ->orderBy('order_date')
            ->orderBy('created_at')
            ->get();

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
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM agar terbuka rapi di Microsoft Excel

            $this->exportPosDetailCsv($file, $orders, $business, $startDate, $endDate);

            fclose($file);
        }, 200, $headers);
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
            'Meja / Referensi', 'Jumlah Item', 'Subtotal', 'Tipe Diskon', 'Nilai Diskon', 'Diskon',
            'Kode Voucher', 'Diskon Voucher', 'Diskon Poin', 'Pajak (%)', 'Pajak',
            'Service Charge (%)', 'Service Charge', 'Pembulatan', 'Total Bayar', 'Dibayar', 'Kembalian',
            'HPP / Modal', 'Laba Kotor', 'Margin (%)', 'Metode Pembayaran', 'Status',
        ]);
        foreach ($orders as $o) {
            $payMethods = $o->payments->map(fn ($p) => $this->paymentMethodLabel($p->payment_method) . ': ' . number_format((float) $p->amount, 2, ',', '.'))->implode(' | ');
            $margin = $o->total_amount > 0 ? round(($o->total_gross_profit / $o->total_amount) * 100, 2) : 0;

            fputcsv($file, [
                $o->order_number,
                $o->order_date?->format('Y-m-d') ?? $o->created_at?->format('Y-m-d') ?? '-',
                $o->created_at?->format('H:i:s') ?? '-',
                $o->location->name ?? '-',
                $o->user->name ?? '-',
                $o->customer->name ?? $o->customer_name_guest ?? 'Umum',
                $o->customer ? 'Member' : 'Guest / Umum',
                strtoupper(str_replace('_', ' ', (string) $o->order_type)),
                $o->table_or_reference ?? '-',
                (int) round($o->items->sum('quantity')),
                round((float) $o->subtotal, 2),
                $this->discountTypeLabel($o->discount_type),
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
            foreach ($o->items as $it) {
                $totalHppItem = (float) $it->total_hpp > 0 ? (float) $it->total_hpp : ((float) $it->quantity * (float) $it->unit_cost_hpp);
                $profitItem = (float) $it->total_price - $totalHppItem;

                fputcsv($file, [
                    $o->order_number,
                    $o->order_date?->format('Y-m-d') ?? $o->created_at?->format('Y-m-d') ?? '-',
                    $o->location->name ?? '-',
                    $o->user->name ?? '-',
                    $o->customer->name ?? $o->customer_name_guest ?? 'Umum',
                    $it->product_code ?? '-',
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
            $qty = (float) $productItems->sum('quantity');
            $sales = (float) $productItems->sum('total_price');
            $hpp = (float) $productItems->sum(fn ($it) => (float) $it->total_hpp > 0 ? (float) $it->total_hpp : ((float) $it->quantity * (float) $it->unit_cost_hpp));
            $profit = $sales - $hpp;
            $margin = $sales > 0 ? ($profit / $sales) * 100 : 0.0;

            fputcsv($file, [
                $productItems->first()->product_code ?? '-',
                $productItems->first()->product_name,
                $this->formatQty($qty),
                round($sales, 2),
                round($hpp, 2),
                round($profit, 2),
                round($margin, 2) . '%',
            ]);
        }
        fputcsv($file, [
            'TOTAL KESELURUHAN', '',
            $this->formatQty((float) $itemsFlat->sum('quantity')),
            round((float) $itemsFlat->sum('total_price'), 2),
            round((float) $itemsFlat->sum(fn ($it) => (float) $it->total_hpp > 0 ? (float) $it->total_hpp : ((float) $it->quantity * (float) $it->unit_cost_hpp)), 2),
            round((float) $itemsFlat->sum(fn ($it) => (float) $it->total_price - ((float) $it->total_hpp > 0 ? (float) $it->total_hpp : ((float) $it->quantity * (float) $it->unit_cost_hpp))), 2),
            round($grossMarginPercent, 2) . '%',
        ]);
        fputcsv($file, []);
        fputcsv($file, ['--- AKHIR LAPORAN ---']);
    }

    /**
     * Format label tipe diskon POS ('percentage' => Persentase, 'fixed' => Nominal).
     */
    private function discountTypeLabel(?string $type): string
    {
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
