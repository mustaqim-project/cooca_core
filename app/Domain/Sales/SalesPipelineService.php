<?php

declare(strict_types=1);

namespace App\Domain\Sales;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class SalesPipelineService
{
    /**
     * Generate unique concurrency-safe sequence number.
     */
    public function generateQuotationNumber(Business $business): string
    {
        $prefix = 'QUO-' . date('Ym') . '-';
        $latest = Quotation::where('business_id', $business->id)
            ->where('quotation_number', 'LIKE', $prefix . '%')
            ->orderByDesc('quotation_number')
            ->lockForUpdate()
            ->value('quotation_number');

        $nextSeq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique concurrency-safe sequence number for Sales Order.
     */
    public function generateSalesOrderNumber(Business $business): string
    {
        $prefix = 'SO-' . date('Ym') . '-';
        $latest = SalesOrder::where('business_id', $business->id)
            ->where('so_number', 'LIKE', $prefix . '%')
            ->orderByDesc('so_number')
            ->lockForUpdate()
            ->value('so_number');

        $nextSeq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new Quotation with line items.
     */
    public function createQuotation(Business $business, array $data): Quotation
    {
        return DB::transaction(function () use ($business, $data) {
            $number = $data['quotation_number'] ?? $this->generateQuotationNumber($business);

            $subtotal = 0.0;
            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw new InvalidArgumentException('Penawaran harga harus memiliki minimal satu baris item.');
            }

            foreach ($itemsData as $item) {
                $lineQty = (float) ($item['quantity'] ?? 1);
                $linePrice = (float) ($item['unit_price'] ?? 0);
                $lineDiscount = (float) ($item['discount_amount'] ?? 0);
                $subtotal += ($lineQty * $linePrice) - $lineDiscount;
            }

            $overallDiscount = (float) ($data['discount_amount'] ?? 0);
            $taxPercentage = array_key_exists('tax_percentage', $data)
                ? (float) $data['tax_percentage']
                : ($business->pos_enable_tax ? (float) $business->pos_tax_percent : 0.0);
            $taxableAmount = max(0.0, $subtotal - $overallDiscount);
            $taxAmount = ($taxableAmount * $taxPercentage) / 100.0;
            $totalAmount = max(0, ($subtotal - $overallDiscount) + $taxAmount);

            $quotation = Quotation::create([
                'business_id' => $business->id,
                'customer_id' => $data['customer_id'],
                'quotation_number' => $number,
                'date' => $data['date'] ?? Carbon::today()->toDateString(),
                'expiry_date' => $data['expiry_date'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $overallDiscount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => $data['status'] ?? Quotation::STATUS_SENT,
                'notes' => $data['notes'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $lineQty = (float) ($item['quantity'] ?? 1);
                $linePrice = (float) ($item['unit_price'] ?? 0);
                $lineDiscount = (float) ($item['discount_amount'] ?? 0);
                $lineSubtotal = ($lineQty * $linePrice) - $lineDiscount;

                $product = !empty($item['product_id']) ? Product::find($item['product_id']) : null;
                $name = $item['product_name'] ?? ($product ? $product->name : 'Item Penawaran');

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $product?->id,
                    'product_name' => $name,
                    'unit_price' => $linePrice,
                    'quantity' => $lineQty,
                    'discount_amount' => $lineDiscount,
                    'subtotal' => $lineSubtotal,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $quotation->load('items', 'customer');
        });
    }

    /**
     * 1-Click Convert Quotation to Sales Order.
     */
    public function convertQuotationToSalesOrder(Quotation $quotation): SalesOrder
    {
        return DB::transaction(function () use ($quotation) {
            $business = $quotation->business;
            $soNumber = $this->generateSalesOrderNumber($business);

            $salesOrder = SalesOrder::create([
                'business_id' => $business->id,
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'so_number' => $soNumber,
                'order_date' => Carbon::today()->toDateString(),
                'expected_delivery_date' => Carbon::today()->addDays(3)->toDateString(),
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount,
                'tax_amount' => $quotation->tax_amount,
                'total_amount' => $quotation->total_amount,
                'status' => SalesOrder::STATUS_CONFIRMED,
                'notes' => "Dikonversi dari Penawaran: {$quotation->quotation_number}",
            ]);

            foreach ($quotation->items as $qItem) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $qItem->product_id,
                    'product_name' => $qItem->product_name,
                    'unit_price' => $qItem->unit_price,
                    'quantity' => $qItem->quantity,
                    'fulfilled_quantity' => 0,
                    'discount_amount' => $qItem->discount_amount,
                    'subtotal' => $qItem->subtotal,
                    'notes' => $qItem->notes,
                ]);
            }

            $quotation->update(['status' => Quotation::STATUS_ACCEPTED]);

            return $salesOrder->load('items', 'customer');
        });
    }

    /**
     * Create direct Sales Order.
     */
    public function createSalesOrder(Business $business, array $data): SalesOrder
    {
        return DB::transaction(function () use ($business, $data) {
            $soNumber = $data['so_number'] ?? $this->generateSalesOrderNumber($business);

            $subtotal = 0.0;
            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw new InvalidArgumentException('Pesanan penjualan harus memiliki minimal satu baris item.');
            }

            foreach ($itemsData as $item) {
                $lineQty = (float) ($item['quantity'] ?? 1);
                $linePrice = (float) ($item['unit_price'] ?? 0);
                $lineDiscount = (float) ($item['discount_amount'] ?? 0);
                $subtotal += ($lineQty * $linePrice) - $lineDiscount;
            }

            $overallDiscount = (float) ($data['discount_amount'] ?? 0);
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $totalAmount = max(0, ($subtotal - $overallDiscount) + $taxAmount);

            $salesOrder = SalesOrder::create([
                'business_id' => $business->id,
                'customer_id' => $data['customer_id'],
                'quotation_id' => $data['quotation_id'] ?? null,
                'so_number' => $soNumber,
                'order_date' => $data['order_date'] ?? Carbon::today()->toDateString(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $overallDiscount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => $data['status'] ?? SalesOrder::STATUS_CONFIRMED,
                'shipping_address' => $data['shipping_address'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $lineQty = (float) ($item['quantity'] ?? 1);
                $linePrice = (float) ($item['unit_price'] ?? 0);
                $lineDiscount = (float) ($item['discount_amount'] ?? 0);
                $lineSubtotal = ($lineQty * $linePrice) - $lineDiscount;

                $product = !empty($item['product_id']) ? Product::find($item['product_id']) : null;
                $name = $item['product_name'] ?? ($product ? $product->name : 'Item Pesanan');

                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $product?->id,
                    'product_name' => $name,
                    'unit_price' => $linePrice,
                    'quantity' => $lineQty,
                    'fulfilled_quantity' => 0,
                    'discount_amount' => $lineDiscount,
                    'subtotal' => $lineSubtotal,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $salesOrder->load('items', 'customer');
        });
    }

    /**
     * 1-Click Generate Official Invoice from Sales Order.
     */
    public function generateInvoiceFromSalesOrder(SalesOrder $salesOrder, ?array $quantitiesToBill = null): Invoice
    {
        return DB::transaction(function () use ($salesOrder, $quantitiesToBill) {
            $business = $salesOrder->business;

            // Generate invoice number
            $invPrefix = 'INV-' . date('Ym') . '-';
            $latest = Invoice::where('business_id', $business->id)
                ->where('invoice_number', 'LIKE', $invPrefix . '%')
                ->orderByDesc('invoice_number')
                ->lockForUpdate()
                ->value('invoice_number');

            $nextSeq = 1;
            if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
                $nextSeq = ((int) $m[1]) + 1;
            }
            $invNumber = $invPrefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);

            $subtotal = 0.0;
            $totalHpp = 0.0;
            $itemsToCreate = [];

            $isFullyFulfilled = true;

            foreach ($salesOrder->items as $soItem) {
                $billQty = $quantitiesToBill[$soItem->id] ?? $soItem->quantity;
                $billQty = (float) $billQty;

                if ($billQty <= 0) {
                    continue;
                }

                $product = $soItem->product;
                $costHpp = $product ? (float) $product->base_cost : 0.0;
                $lineSubtotal = ($billQty * (float) $soItem->unit_price) - (float) $soItem->discount_amount;
                $lineTotalHpp = $costHpp * $billQty;

                $subtotal += $lineSubtotal;
                $totalHpp += $lineTotalHpp;

                $defaultUnitId = $product?->output_unit_id ?? Unit::where('business_id', $business->id)->first()?->id;

                $itemsToCreate[] = [
                    'product_id' => $product?->id,
                    'item_name' => $soItem->product_name,
                    'quantity' => $billQty,
                    'unit_id' => $defaultUnitId,
                    'unit_price' => (float) $soItem->unit_price,
                    'unit_hpp' => $costHpp,
                    'subtotal' => $lineSubtotal,
                    'total_hpp' => $lineTotalHpp,
                ];

                // Update SO fulfilled quantity
                $newFulfilled = (float) $soItem->fulfilled_quantity + $billQty;
                $soItem->update(['fulfilled_quantity' => $newFulfilled]);

                if ($newFulfilled < (float) $soItem->quantity) {
                    $isFullyFulfilled = false;
                }
            }

            $discount = (float) $salesOrder->discount_amount;
            $tax = (float) $salesOrder->tax_amount;
            $total = max(0, ($subtotal - $discount) + $tax);
            $grossProfit = ($subtotal - $discount) - $totalHpp;

            $invoice = Invoice::create([
                'business_id' => $business->id,
                'customer_id' => $salesOrder->customer_id,
                'sales_order_id' => $salesOrder->id,
                'invoice_number' => $invNumber,
                'invoice_date' => Carbon::today()->toDateString(),
                'due_date' => Carbon::today()->addDays(14)->toDateString(),
                'status' => Invoice::STATUS_UNPAID,
                'subtotal' => $subtotal,
                'discount_type' => 'fixed',
                'discount_value' => $discount,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'paid_amount' => 0,
                'balance_due' => $total,
                'total_hpp_cost' => $totalHpp,
                'total_gross_profit' => $grossProfit,
                'payment_terms' => 'Net 14',
                'notes' => "Diterbitkan dari Pesanan Penjualan: {$salesOrder->so_number}",
            ]);

            foreach ($itemsToCreate as $item) {
                InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
            }

            // Update sales order status
            $salesOrder->update([
                'status' => $isFullyFulfilled ? SalesOrder::STATUS_FULFILLED : SalesOrder::STATUS_PARTIALLY_FULFILLED,
            ]);

            return $invoice->load('items', 'customer');
        });
    }
}
