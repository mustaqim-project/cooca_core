<?php

declare(strict_types=1);

namespace App\Domain\Commerce;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Inventory\StockService;
use App\Models\Invoice;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class SalesReturnService
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly AutoJournalService $journalService = new AutoJournalService,
    ) {}

    /** @param array<int, array{invoice_item_id: string, quantity: float|int}> $items */
    public function createFromInvoice(Invoice $invoice, array $items, array $attributes = []): SalesReturn
    {
        return DB::transaction(function () use ($invoice, $items, $attributes): SalesReturn {
            $invoice = Invoice::with('items')->lockForUpdate()->findOrFail($invoice->id);
            if (in_array($invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID], true)) {
                throw new InvalidArgumentException('Hanya invoice yang sudah dirilis dan belum void yang dapat diretur.');
            }
            if (! $invoice->location_id) {
                throw new InvalidArgumentException('Invoice tidak memiliki lokasi stok.');
            }

            $return = SalesReturn::create([
                'business_id' => $invoice->business_id,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'location_id' => $invoice->location_id,
                'return_number' => $attributes['return_number'] ?? 'SR-' . now()->format('Ym') . '-' . Str::upper(Str::random(8)),
                'return_date' => $attributes['return_date'] ?? Carbon::today()->toDateString(),
                'reason' => $attributes['reason'] ?? 'Retur barang',
                'status' => SalesReturn::STATUS_DRAFT,
                'refund_method' => $attributes['refund_method'] ?? SalesReturn::REFUND_CREDIT_NOTE,
                'created_by' => $attributes['created_by'] ?? null,
            ]);

            $total = 0.0;
            foreach ($items as $row) {
                $source = $invoice->items->firstWhere('id', $row['invoice_item_id'] ?? null);
                $quantity = (float) ($row['quantity'] ?? 0);
                if (! $source || $quantity <= 0) {
                    throw new InvalidArgumentException('Item retur tidak valid.');
                }
                $returned = (float) SalesReturnItem::where('invoice_item_id', $source->id)
                    ->whereHas('salesReturn', fn ($query) => $query->where('status', SalesReturn::STATUS_COMPLETED))
                    ->sum('quantity');
                if ($quantity > (float) $source->quantity - $returned + 0.00005) {
                    throw new InvalidArgumentException("Quantity retur melebihi sisa item {$source->item_name}.");
                }
                $subtotal = $quantity * (float) $source->unit_price;
                $total += $subtotal;
                SalesReturnItem::create([
                    'sales_return_id' => $return->id,
                    'invoice_item_id' => $source->id,
                    'product_id' => $source->product_id,
                    'item_name' => $source->item_name,
                    'quantity' => $quantity,
                    'unit_price' => $source->unit_price,
                    'unit_hpp' => $source->unit_hpp,
                    'subtotal' => $subtotal,
                ]);
            }
            if ($total <= 0) {
                throw new InvalidArgumentException('Retur harus memiliki nilai lebih besar dari nol.');
            }
            $return->update(['total_amount' => $total]);
            return $return->fresh('items');
        });
    }

    public function approve(SalesReturn $return, ?string $userId = null): SalesReturn
    {
        if ($return->status !== SalesReturn::STATUS_DRAFT) {
            return $return;
        }
        $return->update(['status' => SalesReturn::STATUS_APPROVED, 'approved_by' => $userId, 'approved_at' => now()]);
        return $return->fresh();
    }

    /** @param array<int, array{pos_order_item_id: string, quantity: float|int}> $items */
    public function createFromPosOrder(PosOrder $order, array $items, array $attributes = []): SalesReturn
    {
        return DB::transaction(function () use ($order, $items, $attributes): SalesReturn {
            $order = PosOrder::with('items')->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== PosOrder::STATUS_COMPLETED && $order->status !== PosOrder::STATUS_PARTIAL_REFUND) {
                throw new InvalidArgumentException('Hanya transaksi POS completed yang dapat direfund.');
            }
            $return = SalesReturn::create([
                'business_id' => $order->business_id, 'pos_order_id' => $order->id, 'customer_id' => $order->customer_id,
                'location_id' => $order->location_id, 'return_number' => $attributes['return_number'] ?? 'PRF-' . now()->format('Ym') . '-' . Str::upper(Str::random(8)),
                'return_date' => $attributes['return_date'] ?? Carbon::today()->toDateString(), 'reason' => $attributes['reason'] ?? 'Refund POS',
                'status' => SalesReturn::STATUS_DRAFT, 'total_amount' => 0, 'refund_method' => SalesReturn::REFUND_CASH,
                'created_by' => $attributes['created_by'] ?? null,
            ]);
            $total = 0.0;
            foreach ($items as $row) {
                $source = $order->items->firstWhere('id', $row['pos_order_item_id'] ?? null);
                $quantity = (float) ($row['quantity'] ?? 0);
                if (! $source || $quantity <= 0) throw new InvalidArgumentException('Item refund POS tidak valid.');
                $returned = (float) SalesReturnItem::where('pos_order_item_id', $source->id)->whereHas('salesReturn', fn ($query) => $query->where('status', SalesReturn::STATUS_COMPLETED))->sum('quantity');
                if ($quantity > (float) $source->quantity - $returned + 0.00005) throw new InvalidArgumentException("Quantity refund melebihi sisa item {$source->product_name}.");
                $subtotal = $quantity * (float) $source->unit_price; $total += $subtotal;
                SalesReturnItem::create(['sales_return_id' => $return->id, 'pos_order_item_id' => $source->id, 'product_id' => $source->product_id, 'item_name' => $source->product_name, 'quantity' => $quantity, 'unit_price' => $source->unit_price, 'unit_hpp' => $source->unit_cost_hpp, 'subtotal' => $subtotal]);
            }
            $return->update(['total_amount' => $total]);
            return $return->fresh('items');
        });
    }

    public function complete(SalesReturn $return, ?string $userId = null): SalesReturn
    {
        return DB::transaction(function () use ($return, $userId): SalesReturn {
            $return = SalesReturn::with(['items', 'invoice'])->lockForUpdate()->findOrFail($return->id);
            if ($return->status === SalesReturn::STATUS_COMPLETED) {
                return $return;
            }
            if ($return->status !== SalesReturn::STATUS_APPROVED) {
                throw new InvalidArgumentException('Retur harus disetujui sebelum diselesaikan.');
            }
            foreach ($return->items as $item) {
                if ($item->product_id) {
                    if ($return->pos_order_id) {
                        $this->stockService->restoreForSalesReturn($return->business_id, $return->location_id, $item->product_id, $item->quantity, $item->unit_hpp, $return->id, $return->return_number, $userId);
                    } else {
                        $this->stockService->restoreForInvoiceReturn($return->business_id, $return->location_id, $item->product_id, $item->quantity, $item->unit_hpp, $return->invoice_id, $return->return_number, $userId);
                    }
                }
            }
            $return->update(['status' => SalesReturn::STATUS_COMPLETED, 'completed_by' => $userId, 'completed_at' => now()]);
            if ($return->pos_order_id) {
                $order = $return->posOrder()->with('items')->lockForUpdate()->firstOrFail();
                $returnedQuantity = (float) $return->posOrder->items->sum('quantity');
                $completedQuantity = (float) SalesReturnItem::whereHas('salesReturn', fn ($query) => $query->where('pos_order_id', $order->id)->where('status', SalesReturn::STATUS_COMPLETED))->sum('quantity');
                $order->update(['status' => $completedQuantity >= $returnedQuantity ? PosOrder::STATUS_REFUNDED : PosOrder::STATUS_PARTIAL_REFUND]);
            }
            $return->pos_order_id
                ? $this->journalService->recordPosRefundJournal($return)
                : $this->journalService->recordSalesReturnJournal($return);
            return $return->fresh('items');
        });
    }
}
