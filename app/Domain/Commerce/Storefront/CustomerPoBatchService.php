<?php

declare(strict_types=1);

namespace App\Domain\Commerce\Storefront;

use App\Domain\Inventory\StockService;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderBatch;
use App\Models\CommerceOrderItem;
use App\Models\CommerceStoreSetting;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class CustomerPoBatchService
{
    public function __construct(
        private readonly CommerceOrderService $orderService,
        private readonly StockService $stockService,
    ) {}

    /**
     * Create a Customer Purchase Order (PO) with scheduled multi-drop delivery batches.
     *
     * @param  array{
     *     name: string,
     *     phone: string,
     *     email?: string|null,
     *     company_name?: string|null,
     *     customer_po_number?: string|null,
     *     address?: string|null,
     *     notes?: string|null
     * }  $customerData
     * @param  array<int, array{
     *     product_id?: string|null,
     *     product_name: string,
     *     product_type?: string,
     *     unit_price?: float|int|null,
     *     quantity: float|int,
     *     notes?: string|null
     * }>  $itemsData
     * @param  array<int, array{
     *     scheduled_date: string,
     *     scheduled_time_slot?: string|null,
     *     quantity?: float|int|null,
     *     shipping_address?: string|null,
     *     notes?: string|null
     * }>  $batchesData
     * @param  array{
     *     order_notes?: string|null,
     *     shipping_rule_id?: string|null,
     *     status?: string|null
     * }  $options
     */
    public function createCustomerPoWithBatches(
        Business $business,
        array $customerData,
        array $itemsData,
        array $batchesData,
        ?string $paymentMethodId = null,
        array $options = []
    ): CommerceOrder {
        $setting = CommerceStoreSetting::where('business_id', $business->id)->first();
        if ($setting && ! $setting->allow_customer_po) {
            throw new RuntimeException('Toko ini sedang tidak menerima pesanan Purchase Order (PO).');
        }

        if (empty($itemsData)) {
            throw new InvalidArgumentException('Daftar item PO tidak boleh kosong.');
        }

        return DB::transaction(function () use ($business, $customerData, $itemsData, $batchesData, $paymentMethodId, $options): CommerceOrder {
            $isMultiBatch = count($batchesData) > 1;
            $orderType = $isMultiBatch ? CommerceOrder::TYPE_PO_BATCH : CommerceOrder::TYPE_CUSTOMER_PO;

            // Generate unique numbers
            $today = Carbon::today()->format('Ymd');
            $randomStr = strtoupper(Str::random(4));
            $orderNumber = "PO-{$today}-{$randomStr}";
            $trackingToken = Str::random(40);

            $order = new CommerceOrder();
            $order->business_id = $business->id;
            $order->location_id = null;
            $order->customer_id = null;
            $order->payment_method_id = $paymentMethodId;
            $order->shipping_rule_id = $options['shipping_rule_id'] ?? null;
            $order->order_number = $orderNumber;
            $order->tracking_token = $trackingToken;
            $order->order_type = $orderType;
            $order->fulfillment_type = CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY;
            $order->status = $options['status'] ?? CommerceOrder::STATUS_PENDING_REVIEW;
            $order->payment_status = CommerceOrder::PAYMENT_UNPAID;

            $order->customer_name = trim($customerData['name']);
            $order->customer_phone = $this->orderService->normalizePhone($customerData['phone']);
            $order->customer_email = $customerData['email'] ?? null;
            $order->company_name = $customerData['company_name'] ?? null;
            $order->customer_po_number = $customerData['customer_po_number'] ?? null;
            $order->shipping_address = $customerData['address'] ?? null;
            $order->notes = $options['order_notes'] ?? ($customerData['notes'] ?? null);

            $order->subtotal = 0;
            $order->shipping_cost = 0;
            $order->discount_amount = 0;
            $order->total_amount = 0;
            $order->save();

            // Insert Items
            $subtotal = 0.0;
            foreach ($itemsData as $item) {
                $product = ! empty($item['product_id'])
                    ? Product::where('business_id', $business->id)->find($item['product_id'])
                    : null;

                $productName = $product ? $product->name : $item['product_name'];
                $productType = $product ? ($product->isService() ? 'service' : 'goods') : ($item['product_type'] ?? 'goods');
                $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : ($product ? (float) $product->selling_price : 0.0);
                $qty = (float) $item['quantity'];
                $lineSubtotal = round($unitPrice * $qty, 2);

                $orderItem = new CommerceOrderItem();
                $orderItem->commerce_order_id = $order->id;
                $orderItem->product_id = $product?->id;
                $orderItem->product_name = $productName;
                $orderItem->product_type = $productType;
                $orderItem->unit_price = $unitPrice;
                $orderItem->quantity = $qty;
                $orderItem->subtotal = $lineSubtotal;
                $orderItem->notes = $item['notes'] ?? null;
                $orderItem->save();

                $subtotal += $lineSubtotal;
            }

            $order->subtotal = $subtotal;
            $order->total_amount = $subtotal + (float) $order->shipping_cost;
            $order->save();

            // Insert Multi-Drop Batches
            $batchIndex = 1;
            foreach ($batchesData as $bData) {
                $batchCode = 'BATCH-' . str_pad((string) $batchIndex, 2, '0', STR_PAD_LEFT);
                $batch = new CommerceOrderBatch();
                $batch->commerce_order_id = $order->id;
                $batch->batch_number = $batchIndex;
                $batch->batch_code = $batchCode;
                $batch->scheduled_date = $bData['scheduled_date'];
                $batch->scheduled_time_slot = $bData['scheduled_time_slot'] ?? null;
                $batch->quantity = isset($bData['quantity']) ? (float) $bData['quantity'] : 1.0;
                $batch->status = CommerceOrderBatch::STATUS_SCHEDULED;
                $batch->shipping_address = $bData['shipping_address'] ?? $order->shipping_address;
                $batch->notes = $bData['notes'] ?? null;
                $batch->save();

                $batchIndex++;
            }

            return $order->load(['items', 'batches']);
        });
    }

    /**
     * Add a new batch drop to an existing Customer PO.
     */
    public function addBatchToOrder(CommerceOrder $order, array $batchData): CommerceOrderBatch
    {
        $nextNumber = (int) $order->batches()->max('batch_number') + 1;
        $batchCode = 'BATCH-' . str_pad((string) $nextNumber, 2, '0', STR_PAD_LEFT);

        return $order->batches()->create([
            'batch_number' => $nextNumber,
            'batch_code' => $batchCode,
            'scheduled_date' => $batchData['scheduled_date'],
            'scheduled_time_slot' => $batchData['scheduled_time_slot'] ?? null,
            'quantity' => isset($batchData['quantity']) ? (float) $batchData['quantity'] : 1.0,
            'status' => CommerceOrderBatch::STATUS_SCHEDULED,
            'shipping_address' => $batchData['shipping_address'] ?? $order->shipping_address,
            'notes' => $batchData['notes'] ?? null,
        ]);
    }

    /**
     * Update status of a specific batch and automatically update parent order if fully delivered.
     */
    public function updateBatchStatus(
        CommerceOrderBatch $batch,
        string $status,
        ?string $trackingNumber = null,
        ?string $notes = null
    ): CommerceOrderBatch {
        $allowed = [
            CommerceOrderBatch::STATUS_SCHEDULED,
            CommerceOrderBatch::STATUS_IN_PREPARATION,
            CommerceOrderBatch::STATUS_SHIPPED,
            CommerceOrderBatch::STATUS_DELIVERED,
            CommerceOrderBatch::STATUS_CANCELLED,
        ];

        if (! in_array($status, $allowed, true)) {
            throw new InvalidArgumentException("Status batch '{$status}' tidak valid.");
        }

        return DB::transaction(function () use ($batch, $status, $trackingNumber, $notes): CommerceOrderBatch {
            $updateData = ['status' => $status];

            if ($trackingNumber !== null) {
                $updateData['tracking_number'] = $trackingNumber;
            }

            if ($notes !== null) {
                $updateData['notes'] = $notes;
            }

            if ($status === CommerceOrderBatch::STATUS_DELIVERED && empty($batch->delivered_at)) {
                $updateData['delivered_at'] = Carbon::now();
            }

            $batch->update($updateData);

            // Check parent order batches
            $order = $batch->order;
            if ($order) {
                $allBatches = $order->batches()->get();
                $totalCount = $allBatches->count();
                $deliveredCount = $allBatches->where('status', CommerceOrderBatch::STATUS_DELIVERED)->count();

                if ($totalCount > 0 && $deliveredCount === $totalCount) {
                    // All drops are delivered!
                    if (in_array($order->status, [CommerceOrder::STATUS_PROCESSING, CommerceOrder::STATUS_READY, CommerceOrder::STATUS_PAID], true)) {
                        $order->update(['status' => CommerceOrder::STATUS_COMPLETED]);
                    }
                }
            }

            return $batch->fresh();
        });
    }
}
