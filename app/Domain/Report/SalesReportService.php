<?php

declare(strict_types=1);

namespace App\Domain\Report;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use Carbon\Carbon;

/**
 * Reporting penjualan yang membaca SNAPSHOT harga pada detail transaksi
 * (POS & Invoice), bukan harga master product.
 *
 * Prinsip: "Master price adalah harga yang berlaku saat ini, sedangkan
 * transaction snapshot adalah harga historis yang benar-benar digunakan
 * pada saat transaksi."
 */
final class SalesReportService
{
    /**
     * Ringkasan penjualan berbasis snapshot transaksi.
     *
     * Menghasilkan average harga jual & average harga modal (weighted average
     * berdasarkan quantity), total modal, total penjualan, gross profit dan margin.
     *
     * @return array{
     *     period: array{start_date: string, end_date: string},
     *     summary: array{
     *         total_quantity: float,
     *         total_orders: int,
     *         total_sales: float,
     *         total_modal: float,
     *         total_gross_profit: float,
     *         average_selling_price: float,
     *         average_cost_price: float,
     *         margin_percentage: float,
     *         source: string
     *     },
     *     by_product: array<int, array<string, mixed>>,
     *     daily_trend: array<int, array<string, mixed>>
     * }
     */
    public function summary(string $businessId, ?Carbon $from = null, ?Carbon $to = null, ?string $locationId = null): array
    {
        $fromDate = ($from ?? Carbon::create(1970, 1, 1))->startOfDay()->toDateString();
        $toDate = ($to ?? Carbon::create(9999, 12, 31))->endOfDay()->toDateString();

        $posAgg = $this->posAggregate($businessId, $fromDate, $toDate, $locationId);
        $invAgg = $this->invoiceAggregate($businessId, $fromDate, $toDate);

        $totalQuantity = $posAgg['total_quantity'] + $invAgg['total_quantity'];
        $totalSales = $posAgg['total_sales'] + $invAgg['total_sales'];
        $totalModal = $posAgg['total_modal'] + $invAgg['total_modal'];
        $totalOrders = $posAgg['total_orders'] + $invAgg['total_orders'];

        $grossProfit = $totalSales - $totalModal;

        return [
            'period' => [
                'start_date' => $fromDate,
                'end_date' => $toDate,
            ],
            'summary' => [
                'total_quantity' => round($totalQuantity, 4),
                'total_orders' => $totalOrders,
                'total_sales' => round($totalSales, 2),
                'total_modal' => round($totalModal, 2),
                'total_gross_profit' => round($grossProfit, 2),
                'average_selling_price' => $totalQuantity > 0 ? round($totalSales / $totalQuantity, 2) : 0.0,
                'average_cost_price' => $totalQuantity > 0 ? round($totalModal / $totalQuantity, 2) : 0.0,
                'margin_percentage' => $totalSales > 0 ? round(($grossProfit / $totalSales) * 100, 2) : 0.0,
                'source' => 'transaction_snapshot',
            ],
            'by_product' => $this->productBreakdown($businessId, $fromDate, $toDate, $locationId),
            'daily_trend' => $this->dailyTrend($businessId, $fromDate, $toDate, $locationId),
        ];
    }

    /**
     * Average harga jual & modal (weighted average) dari snapshot transaksi.
     *
     * @return array<string, float|string>
     */
    public function averagePrices(string $businessId, ?Carbon $from = null, ?Carbon $to = null, ?string $locationId = null): array
    {
        $report = $this->summary($businessId, $from, $to, $locationId);

        return [
            'total_quantity' => $report['summary']['total_quantity'],
            'average_selling_price' => $report['summary']['average_selling_price'],
            'average_cost_price' => $report['summary']['average_cost_price'],
            'total_sales' => $report['summary']['total_sales'],
            'total_modal' => $report['summary']['total_modal'],
            'total_gross_profit' => $report['summary']['total_gross_profit'],
            'margin_percentage' => $report['summary']['margin_percentage'],
            'period' => $report['period'],
            'source' => 'transaction_snapshot',
        ];
    }

    /**
     * Ringkasan sales KPI yang ramah untuk dashboard mobile / web.
     *
     * @return array<string, mixed>
     */
    public function kpiSummary(string $businessId, ?Carbon $from = null, ?Carbon $to = null, ?string $locationId = null): array
    {
        $report = $this->summary($businessId, $from, $to, $locationId);

        return [
            'total_sales' => $report['summary']['total_sales'],
            'total_orders' => $report['summary']['total_orders'],
            'total_quantity' => $report['summary']['total_quantity'],
            'total_hpp' => $report['summary']['total_modal'],
            'total_gross_profit' => $report['summary']['total_gross_profit'],
            'average_selling_price' => $report['summary']['average_selling_price'],
            'average_cost_price' => $report['summary']['average_cost_price'],
            'margin_percentage' => $report['summary']['margin_percentage'],
        ];
    }

    /**
     * @return array{total_quantity: float, total_sales: float, total_modal: float, total_orders: int}
     */
    private function posAggregate(string $businessId, string $fromDate, string $toDate, ?string $locationId = null): array
    {
        $row = PosOrderItem::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->where('pos_orders.business_id', $businessId)
            ->where('pos_orders.status', PosOrder::STATUS_COMPLETED)
            ->whereDate('pos_orders.order_date', '>=', $fromDate)
            ->whereDate('pos_orders.order_date', '<=', $toDate)
            ->when($locationId, fn ($q, $id) => $q->where('pos_orders.location_id', $id))
            ->selectRaw('
                COALESCE(SUM(pos_order_items.quantity), 0) as total_quantity,
                COALESCE(SUM(pos_order_items.total_price), 0) as total_sales,
                COALESCE(SUM(pos_order_items.total_hpp), 0) as total_modal,
                COUNT(DISTINCT pos_orders.id) as total_orders
            ')
            ->first();

        return [
            'total_quantity' => (float) ($row->total_quantity ?? 0.0),
            'total_sales' => (float) ($row->total_sales ?? 0.0),
            'total_modal' => (float) ($row->total_modal ?? 0.0),
            'total_orders' => (int) ($row->total_orders ?? 0),
        ];
    }

    /**
     * @return array{total_quantity: float, total_sales: float, total_modal: float, total_orders: int}
     */
    private function invoiceAggregate(string $businessId, string $fromDate, string $toDate): array
    {
        $row = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.business_id', $businessId)
            ->whereNotIn('invoices.status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID])
            ->whereDate('invoices.invoice_date', '>=', $fromDate)
            ->whereDate('invoices.invoice_date', '<=', $toDate)
            ->selectRaw('
                COALESCE(SUM(invoice_items.quantity), 0) as total_quantity,
                COALESCE(SUM(invoice_items.subtotal), 0) as total_sales,
                COALESCE(SUM(invoice_items.total_hpp), 0) as total_modal,
                COUNT(DISTINCT invoices.id) as total_orders
            ')
            ->first();

        return [
            'total_quantity' => (float) ($row->total_quantity ?? 0.0),
            'total_sales' => (float) ($row->total_sales ?? 0.0),
            'total_modal' => (float) ($row->total_modal ?? 0.0),
            'total_orders' => (int) ($row->total_orders ?? 0),
        ];
    }

    /**
     * Breakdown average harga per produk dari snapshot transaksi (weighted average).
     *
     * @return array<int, array<string, mixed>>
     */
    private function productBreakdown(string $businessId, string $fromDate, string $toDate, ?string $locationId = null): array
    {
        $posRows = PosOrderItem::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->where('pos_orders.business_id', $businessId)
            ->where('pos_orders.status', PosOrder::STATUS_COMPLETED)
            ->whereDate('pos_orders.order_date', '>=', $fromDate)
            ->whereDate('pos_orders.order_date', '<=', $toDate)
            ->when($locationId, fn ($q, $id) => $q->where('pos_orders.location_id', $id))
            ->groupBy('pos_order_items.product_id', 'pos_order_items.product_name')
            ->selectRaw('
                pos_order_items.product_id,
                pos_order_items.product_name as item_name,
                COALESCE(SUM(pos_order_items.quantity), 0) as total_quantity,
                COALESCE(SUM(pos_order_items.total_price), 0) as total_sales,
                COALESCE(SUM(pos_order_items.total_hpp), 0) as total_modal
            ')
            ->orderByDesc('total_sales')
            ->get();

        $combined = [];
        foreach ($posRows as $row) {
            $combined[$row->product_id ?? $row->item_name] = [
                'product_id' => $row->product_id,
                'product_name' => $row->item_name,
                'total_quantity' => (float) $row->total_quantity,
                'total_sales' => (float) $row->total_sales,
                'total_modal' => (float) $row->total_modal,
            ];
        }

        $invoiceRows = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.business_id', $businessId)
            ->whereNotIn('invoices.status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID])
            ->whereDate('invoices.invoice_date', '>=', $fromDate)
            ->whereDate('invoices.invoice_date', '<=', $toDate)
            ->groupBy('invoice_items.product_id', 'invoice_items.item_name')
            ->selectRaw('
                invoice_items.product_id,
                invoice_items.item_name,
                COALESCE(SUM(invoice_items.quantity), 0) as total_quantity,
                COALESCE(SUM(invoice_items.subtotal), 0) as total_sales,
                COALESCE(SUM(invoice_items.total_hpp), 0) as total_modal
            ')
            ->get();

        foreach ($invoiceRows as $row) {
            $key = $row->product_id ?? $row->item_name;
            if (! isset($combined[$key])) {
                $combined[$key] = [
                    'product_id' => $row->product_id,
                    'product_name' => $row->item_name,
                    'total_quantity' => 0.0,
                    'total_sales' => 0.0,
                    'total_modal' => 0.0,
                ];
            }
            $combined[$key]['total_quantity'] += (float) $row->total_quantity;
            $combined[$key]['total_sales'] += (float) $row->total_sales;
            $combined[$key]['total_modal'] += (float) $row->total_modal;
        }

        $breakdown = [];
        foreach ($combined as $item) {
            $qty = (float) $item['total_quantity'];
            $sales = (float) $item['total_sales'];
            $modal = (float) $item['total_modal'];

            $breakdown[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'total_quantity' => round($qty, 4),
                'total_sales' => round($sales, 2),
                'total_modal' => round($modal, 2),
                'average_selling_price' => $qty > 0 ? round($sales / $qty, 2) : 0.0,
                'average_cost_price' => $qty > 0 ? round($modal / $qty, 2) : 0.0,
                'gross_profit' => round($sales - $modal, 2),
                'margin_percentage' => $sales > 0 ? round((($sales - $modal) / $sales) * 100, 2) : 0.0,
            ];
        }

        usort($breakdown, fn ($a, $b) => $b['total_sales'] <=> $a['total_sales']);

        return $breakdown;
    }

    /**
     * Tren penjualan harian dari snapshot transaksi (POS + Invoice).
     *
     * @return array<int, array<string, mixed>>
     */
    private function dailyTrend(string $businessId, string $fromDate, string $toDate, ?string $locationId = null): array
    {
        $posRows = PosOrder::query()
            ->where('business_id', $businessId)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereDate('order_date', '>=', $fromDate)
            ->whereDate('order_date', '<=', $toDate)
            ->when($locationId, fn ($q, $id) => $q->where('location_id', $id))
            ->selectRaw('order_date, SUM(total_amount) as total_sales, SUM(total_hpp_cost) as total_modal, COUNT(*) as total_orders')
            ->groupBy('order_date')
            ->get();

        $invoiceRows = Invoice::query()
            ->where('business_id', $businessId)
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID])
            ->whereDate('invoice_date', '>=', $fromDate)
            ->whereDate('invoice_date', '<=', $toDate)
            ->selectRaw('invoice_date as order_date, SUM(subtotal) as total_sales, SUM(total_hpp_cost) as total_modal, COUNT(*) as total_orders')
            ->groupBy('invoice_date')
            ->get();

        $trend = [];
        foreach ($posRows as $row) {
            $trend[(string) $row->order_date] = [
                'date' => (string) $row->order_date,
                'total_sales' => (float) $row->total_sales,
                'total_modal' => (float) $row->total_modal,
                'total_orders' => (int) $row->total_orders,
            ];
        }

        foreach ($invoiceRows as $row) {
            $date = (string) $row->order_date;
            if (! isset($trend[$date])) {
                $trend[$date] = [
                    'date' => $date,
                    'total_sales' => 0.0,
                    'total_modal' => 0.0,
                    'total_orders' => 0,
                ];
            }
            $trend[$date]['total_sales'] += (float) $row->total_sales;
            $trend[$date]['total_modal'] += (float) $row->total_modal;
            $trend[$date]['total_orders'] += (int) $row->total_orders;
        }

        ksort($trend);

        return array_map(function (array $day): array {
            $day['total_sales'] = round($day['total_sales'], 2);
            $day['total_modal'] = round($day['total_modal'], 2);

            return $day;
        }, array_values($trend));
    }
}