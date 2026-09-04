<?php

declare(strict_types=1);

namespace App\Domain\Purchasing;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Inventory\StockService;
use App\Models\GoodsReceipt;
use App\Models\PurchaseReturn as PurchaseReturnModel;
use App\Models\PurchaseReturnItem;
use App\Models\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class PurchaseReturnService
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly AutoJournalService $journalService = new AutoJournalService,
    ) {}

    /** @param array<int, array{goods_receipt_item_id: string, quantity: float|int}> $items */
    public function createFromGoodsReceipt(GoodsReceipt $receipt, array $items, array $attributes = []): PurchaseReturnModel
    {
        return DB::transaction(function () use ($receipt, $items, $attributes): PurchaseReturnModel {
            $receipt = GoodsReceipt::with(['items', 'supplierInvoice'])->lockForUpdate()->findOrFail($receipt->id);
            if ($receipt->status !== 'completed' || ! $receipt->supplier_id) {
                throw new InvalidArgumentException('Goods Receipt belum selesai atau tidak memiliki supplier.');
            }
            $supplierInvoice = $receipt->supplierInvoice;
            if ($supplierInvoice && (float) $supplierInvoice->balance_due <= 0) {
                throw new InvalidArgumentException('Hutang supplier sudah lunas; retur memerlukan proses credit balance tersendiri.');
            }

            $return = PurchaseReturnModel::create([
                'business_id' => $receipt->business_id,
                'goods_receipt_id' => $receipt->id,
                'supplier_id' => $receipt->supplier_id,
                'supplier_invoice_id' => $supplierInvoice?->id,
                'location_id' => $receipt->location_id,
                'return_number' => $attributes['return_number'] ?? 'PR-' . now()->format('Ym') . '-' . Str::upper(Str::random(8)),
                'return_date' => $attributes['return_date'] ?? Carbon::today()->toDateString(),
                'reason' => $attributes['reason'] ?? 'Retur barang ke supplier',
                'status' => PurchaseReturnModel::STATUS_DRAFT,
                'debit_note_number' => $attributes['debit_note_number'] ?? null,
                'created_by' => $attributes['created_by'] ?? null,
            ]);

            $total = 0.0;
            foreach ($items as $row) {
                $source = $receipt->items->firstWhere('id', $row['goods_receipt_item_id'] ?? null);
                $quantity = (float) ($row['quantity'] ?? 0);
                if (! $source || $quantity <= 0) {
                    throw new InvalidArgumentException('Item retur pembelian tidak valid.');
                }
                $returned = (float) PurchaseReturnItem::where('goods_receipt_item_id', $source->id)
                    ->whereHas('purchaseReturn', fn ($query) => $query->where('status', PurchaseReturnModel::STATUS_COMPLETED))
                    ->sum('quantity');
                if ($quantity > (float) $source->quantity - $returned + 0.00005) {
                    throw new InvalidArgumentException("Quantity retur melebihi sisa item {$source->product_id}.");
                }
                $subtotal = $quantity * (float) $source->unit_cost;
                $total += $subtotal;
                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'goods_receipt_item_id' => $source->id,
                    'product_id' => $source->product_id,
                    'item_name' => $source->product?->name ?? 'Produk',
                    'quantity' => $quantity,
                    'unit_cost' => $source->unit_cost,
                    'subtotal' => $subtotal,
                ]);
            }
            if ($total <= 0) {
                throw new InvalidArgumentException('Retur harus memiliki nilai lebih besar dari nol.');
            }
            if ($supplierInvoice && $total > (float) $supplierInvoice->balance_due + 0.005) {
                throw new InvalidArgumentException('Nilai retur melebihi saldo hutang supplier.');
            }
            $return->update(['total_amount' => $total]);
            return $return->fresh('items');
        });
    }

    public function approve(PurchaseReturnModel $return, ?string $userId = null): PurchaseReturnModel
    {
        if ($return->status !== PurchaseReturnModel::STATUS_DRAFT) {
            return $return;
        }
        $return->update(['status' => PurchaseReturnModel::STATUS_APPROVED, 'approved_by' => $userId, 'approved_at' => now()]);
        return $return->fresh();
    }

    public function complete(PurchaseReturnModel $return, ?string $userId = null): PurchaseReturnModel
    {
        return DB::transaction(function () use ($return, $userId): PurchaseReturnModel {
            $return = PurchaseReturnModel::with(['items', 'supplierInvoice'])->lockForUpdate()->findOrFail($return->id);
            if ($return->status === PurchaseReturnModel::STATUS_COMPLETED) {
                return $return;
            }
            if ($return->status !== PurchaseReturnModel::STATUS_APPROVED) {
                throw new InvalidArgumentException('Retur harus disetujui sebelum diselesaikan.');
            }
            foreach ($return->items as $item) {
                $this->stockService->deductForPurchaseReturn($return->business_id, $return->location_id, $item->product_id, $item->quantity, $item->unit_cost, $return->id, $return->return_number, $userId);
            }
            if ($return->supplierInvoice) {
                $invoice = SupplierInvoice::lockForUpdate()->findOrFail($return->supplierInvoice->id);
                $invoice->update([
                    'total_amount' => max(0.0, (float) $invoice->total_amount - (float) $return->total_amount),
                    'balance_due' => max(0.0, (float) $invoice->balance_due - (float) $return->total_amount),
                ]);
                if ((float) $invoice->balance_due <= 0.005) {
                    $invoice->update(['status' => SupplierInvoice::STATUS_PAID]);
                }
            }
            $return->update(['status' => PurchaseReturnModel::STATUS_COMPLETED, 'completed_by' => $userId, 'completed_at' => now()]);
            $this->journalService->recordPurchaseReturnJournal($return);
            return $return->fresh('items');
        });
    }
}
