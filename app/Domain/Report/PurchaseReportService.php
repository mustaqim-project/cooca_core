<?php

declare(strict_types=1);

namespace App\Domain\Report;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Carbon\Carbon;

/**
 * Reporting pembelian yang membaca SNAPSHOT harga beli pada detail PO.
 *
 * Snapshot yang dibaca adalah `purchase_order_items.purchase_price_snapshot`
 * (harga beli pada saat PO dibuat), sehingga perubahan harga master
 * product/supplier di masa depan tidak mengubah histori & average harga.
 */
final class PurchaseReportService
{
    /**
     * Ringkasan pembelian berbasis snapshot PO.
     *
     * @return array{
     *     period: array{start_date: string, end_date: string},
     *     summary: array{
     *         total_quantity: float,
     *         total_transactions: int,
     *         total_value: float,
     *         average_buy_price: float,
     *         min_buy_price: float,
     *         max_buy_price: float,
     *         source: string
     *     },
     *     by_product: array<int, array<string, mixed>>,
     *     by_supplier: array<int, array<string, mixed>>,
     *     by_period: array<int, array<string, mixed>>
     * }
     */
    public function summary(string $businessId, ?Carbon $from = null, ?Carbon $to = null, ?string $supplierId = null): array
    {
        $fromDate = ($from ?? Carbon::create(1970, 1, 1))->startOfDay()->toDateString();
        $toDate = ($to ?? Carbon::create(9999, 12, 31))->endOfDay()->toDateString();

        $base = fn () => PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->where('purchase_orders.business_id', $businessId)
            ->where('purchase_orders.po_type', PurchaseOrder::TYPE_SUPPLIER)
            ->whereNotIn('purchase_orders.status', [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_CANCELLED])
            ->whereBetween('purchase_orders.order_date', [$fromDate, $toDate])
            ->when($supplierId, fn ($q, $id) => $q->where('purchase_orders.supplier_id', $id));

        $agg = $base()
            ->selectRaw('
                COALESCE(SUM(purchase_order_items.quantity), 0) as total_quantity,
                COALESCE(SUM(purchase_order_items.purchase_price_snapshot * purchase_order_items.quantity), 0) as total_value,
                COALESCE(MIN(purchase_order_items.purchase_price_snapshot), 0) as min_price,
                COALESCE(MAX(purchase_order_items.purchase_price_snapshot), 0) as max_price,
                COUNT(DISTINCT purchase_orders.id) as total_transactions
            ')
            ->first();

        $totalQuantity = (float) ($agg->total_quantity ?? 0.0);
        $totalValue = (float) ($agg->total_value ?? 0.0);

        return [
            'period' => [
                'start_date' => $fromDate,
                'end_date' => $toDate,
            ],
            'summary' => [
                'total_quantity' => round($totalQuantity, 4),
                'total_transactions' => (int) ($agg->total_transactions ?? 0),
                'total_value' => round($totalValue, 2),
                'average_buy_price' => $totalQuantity > 0 ? round($totalValue / $totalQuantity, 2) : 0.0,
                'min_buy_price' => round((float) ($agg->min_price ?? 0.0), 2),
                'max_buy_price' => round((float) ($agg->max_price ?? 0.0), 2),
                'source' => 'purchase_order_snapshot',
            ],
            'by_product' => $this->byProduct($businessId, $fromDate, $toDate, $supplierId),
            'by_supplier' => $this->bySupplier($businessId, $fromDate, $toDate, $supplierId),
            'by_period' => $this->byPeriod($businessId, $fromDate, $toDate, $supplierId),
        ];
    }

    /**
     * Riwayat perubahan harga beli per produk (chronological).
     *
     * @return array<int, array<string, mixed>>
     */
    public function history(string $businessId, ?string $productId = null, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $fromDate = ($from ?? Carbon::create(1970, 1, 1))->startOfDay()->toDateString();
        $toDate = ($to ?? Carbon::create(9999, 12, 31))->endOfDay()->toDateString();

        $items = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->where('purchase_orders.business_id', $businessId)
            ->where('purchase_orders.po_type', PurchaseOrder::TYPE_SUPPLIER)
            ->whereNotIn('purchase_orders.status', [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_CANCELLED])
            ->whereBetween('purchase_orders.order_date', [$fromDate, $toDate])
            ->when($productId, fn ($q, $id) => $q->where('purchase_order_items.product_id', $id))
            ->selectRaw('
                purchase_orders.order_date,
                purchase_orders.po_number,
                purchase_orders.id as purchase_order_id,
                purchase_order_items.product_id,
                purchase_order_items.item_name as product_name,
                COALESCE(purchase_order_items.supplier_name_snapshot, suppliers.name, \'Tanpa Supplier\') as supplier_name,
                purchase_order_items.quantity,
                purchase_order_items.purchase_price_snapshot,
                (purchase_order_items.purchase_price_snapshot * purchase_order_items.quantity) as total_value
            ')
            ->orderBy('purchase_orders.order_date')
            ->orderBy('purchase_orders.po_number')
            ->get();

        return $items
            ->map(fn ($row) => [
                'order_date' => (string) $row->order_date,
                'purchase_order_id' => $row->purchase_order_id,
                'po_number' => $row->po_number,
                'product_id' => $row->product_id,
                'product_name' => $row->product_name,
                'supplier_name' => $row->supplier_name,
                'quantity' => round((float) $row->quantity, 4),
                'buy_price' => round((float) $row->purchase_price_snapshot, 2),
                'total_value' => round((float) $row->total_value, 2),
            ])
            ->all();
    }

    /**
     * Average harga beli per produk (weighted average dari snapshot PO).
     *
     * @return array<int, array<string, mixed>>
     */
    private function byProduct(string $businessId, string $fromDate, string $toDate, ?string $supplierId = null): array
    {
        $rows = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->where('purchase_orders.business_id', $businessId)
            ->where('purchase_orders.po_type', PurchaseOrder::TYPE_SUPPLIER)
            ->whereNotIn('purchase_orders.status', [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_CANCELLED])
            ->whereBetween('purchase_orders.order_date', [$fromDate, $toDate])
            ->when($supplierId, fn ($q, $id) => $q->where('purchase_orders.supplier_id', $id))
            ->groupBy('purchase_order_items.product_id', 'purchase_order_items.item_name')
            ->selectRaw('
                purchase_order_items.product_id,
                purchase_order_items.item_name as product_name,
                COALESCE(SUM(purchase_order_items.quantity), 0) as total_quantity,
                COALESCE(SUM(purchase_order_items.purchase_price_snapshot * purchase_order_items.quantity), 0) as total_value,
                COALESCE(MIN(purchase_order_items.purchase_price_snapshot), 0) as min_price,
                COALESCE(MAX(purchase_order_items.purchase_price_snapshot), 0) as max_price,
                COUNT(DISTINCT purchase_orders.id) as transaction_count
            ')
            ->orderByDesc('total_value')
            ->get();

        return $rows
            ->map(function ($row): array {
                $qty = (float) $row->total_quantity;
                $value = (float) $row->total_value;

                return [
                    'product_id' => $row->product_id,
                    'product_name' => $row->product_name,
                    'total_quantity' => round($qty, 4),
                    'total_value' => round($value, 2),
                    'average_buy_price' => $qty > 0 ? round($value / $qty, 2) : 0.0,
                    'min_buy_price' => round((float) $row->min_price, 2),
                    'max_buy_price' => round((float) $row->max_price, 2),
                    'transaction_count' => (int) $row->transaction_count,
                ];
            })
            ->all();
    }

    /**
     * Average harga beli per supplier (weighted average dari snapshot PO).
     *
     * @return array<int, array<string, mixed>>
     */
    private function bySupplier(string $businessId, string $fromDate, string $toDate, ?string $supplierId = null): array
    {
        $rows = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->where('purchase_orders.business_id', $businessId)
            ->where('purchase_orders.po_type', PurchaseOrder::TYPE_SUPPLIER)
            ->whereNotIn('purchase_orders.status', [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_CANCELLED])
            ->whereBetween('purchase_orders.order_date', [$fromDate, $toDate])
            ->when($supplierId, fn ($q, $id) => $q->where('purchase_orders.supplier_id', $id))
            ->groupBy('purchase_orders.supplier_id', 'suppliers.name', 'purchase_order_items.supplier_name_snapshot')
            ->selectRaw('
                purchase_orders.supplier_id,
                COALESCE(purchase_order_items.supplier_name_snapshot, suppliers.name, \'Tanpa Supplier\') as supplier_name,
                COALESCE(SUM(purchase_order_items.quantity), 0) as total_quantity,
                COALESCE(SUM(purchase_order_items.purchase_price_snapshot * purchase_order_items.quantity), 0) as total_value,
                COALESCE(MIN(purchase_order_items.purchase_price_snapshot), 0) as min_price,
                COALESCE(MAX(purchase_order_items.purchase_price_snapshot), 0) as max_price,
                COUNT(DISTINCT purchase_orders.id) as transaction_count
            ')
            ->orderByDesc('total_value')
            ->get();

        return $rows
            ->map(function ($row): array {
                $qty = (float) $row->total_quantity;
                $value = (float) $row->total_value;

                return [
                    'supplier_id' => $row->supplier_id,
                    'supplier_name' => $row->supplier_name,
                    'total_quantity' => round($qty, 4),
                    'total_value' => round($value, 2),
                    'average_buy_price' => $qty > 0 ? round($value / $qty, 2) : 0.0,
                    'min_buy_price' => round((float) $row->min_price, 2),
                    'max_buy_price' => round((float) $row->max_price, 2),
                    'transaction_count' => (int) $row->transaction_count,
                ];
            })
            ->all();
    }

    /**
     * Average harga beli per periode (bulanan) dari snapshot PO.
     *
     * @return array<int, array<string, mixed>>
     */
    private function byPeriod(string $businessId, string $fromDate, string $toDate, ?string $supplierId = null): array
    {
        $rows = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->where('purchase_orders.business_id', $businessId)
            ->where('purchase_orders.po_type', PurchaseOrder::TYPE_SUPPLIER)
            ->whereNotIn('purchase_orders.status', [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_CANCELLED])
            ->whereBetween('purchase_orders.order_date', [$fromDate, $toDate])
            ->when($supplierId, fn ($q, $id) => $q->where('purchase_orders.supplier_id', $id))
            ->selectRaw('
                purchase_orders.order_date,
                purchase_orders.id as purchase_order_id,
                purchase_order_items.quantity,
                purchase_order_items.purchase_price_snapshot
            ')
            ->get();

        $periods = [];
        foreach ($rows as $row) {
            $period = Carbon::parse($row->order_date)->format('Y-m');
            if (! isset($periods[$period])) {
                $periods[$period] = [
                    'period' => $period,
                    'total_quantity' => 0.0,
                    'total_value' => 0.0,
                    'min_price' => null,
                    'max_price' => null,
                    'purchase_order_ids' => [],
                ];
            }

            $qty = (float) $row->quantity;
            $price = (float) $row->purchase_price_snapshot;

            $periods[$period]['total_quantity'] += $qty;
            $periods[$period]['total_value'] += $price * $qty;
            $periods[$period]['min_price'] = $periods[$period]['min_price'] === null
                ? $price
                : min($periods[$period]['min_price'], $price);
            $periods[$period]['max_price'] = $periods[$period]['max_price'] === null
                ? $price
                : max($periods[$period]['max_price'], $price);
            $periods[$period]['purchase_order_ids'][(string) $row->purchase_order_id] = true;
        }

        ksort($periods);

        return array_map(function (array $period): array {
            $qty = $period['total_quantity'];
            $value = $period['total_value'];

            return [
                'period' => $period['period'],
                'total_quantity' => round($qty, 4),
                'total_value' => round($value, 2),
                'average_buy_price' => $qty > 0 ? round($value / $qty, 2) : 0.0,
                'min_buy_price' => round((float) ($period['min_price'] ?? 0.0), 2),
                'max_buy_price' => round((float) ($period['max_price'] ?? 0.0), 2),
                'transaction_count' => count($period['purchase_order_ids']),
            ];
        }, array_values($periods));
    }
}