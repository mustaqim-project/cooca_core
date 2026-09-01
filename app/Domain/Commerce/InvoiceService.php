<?php

declare(strict_types=1);

namespace App\Domain\Commerce;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use InvalidArgumentException;

final class InvoiceService
{
    public function __construct(
        private readonly InvoiceNumberGenerator $numberGenerator = new InvoiceNumberGenerator
    ) {}

    /**
     * Create invoice directly from product items.
     *
     * @param  array<int, array{
     *     product_id?: string|null,
     *     item_name?: string|null,
     *     sku?: string|null,
     *     description?: string|null,
     *     quantity: float|int,
     *     unit_id: string,
     *     unit_price: float|int,
     *     unit_hpp?: float|int|null
     * }>  $itemsData
     * @param  array<string, mixed>  $attributes
     */
    public function createFromProducts(
        Business $business,
        Customer $customer,
        array $itemsData,
        array $attributes = []
    ): Invoice {
        if (empty($itemsData)) {
            throw new InvalidArgumentException('Invoice must contain at least one line item.');
        }

        $invoiceNumber = $attributes['invoice_number'] ?? $this->numberGenerator->generateInvoiceNumber($business);
        $invoiceDate = ! empty($attributes['invoice_date']) ? Carbon::parse($attributes['invoice_date']) : Carbon::today();
        
        $termsDays = (int) ($attributes['payment_terms_days'] ?? $customer->payment_terms_days ?? 30);
        $dueDate = ! empty($attributes['due_date'])
            ? Carbon::parse($attributes['due_date'])
            : $invoiceDate->copy()->addDays($termsDays);

        /** @var Invoice $invoice */
        $invoice = Invoice::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'purchase_order_id' => $attributes['purchase_order_id'] ?? null,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'status' => $attributes['status'] ?? Invoice::STATUS_DRAFT,
            'discount_type' => $attributes['discount_type'] ?? 'fixed',
            'discount_value' => (float) ($attributes['discount_value'] ?? 0.0),
            'tax_percentage' => (float) ($attributes['tax_percentage'] ?? 0.0),
            'shipping_cost' => (float) ($attributes['shipping_cost'] ?? 0.0),
            'payment_terms' => $attributes['payment_terms'] ?? "Net {$termsDays}",
            'notes' => $attributes['notes'] ?? null,
            'terms_conditions' => $attributes['terms_conditions'] ?? null,
            'created_by' => $attributes['created_by'] ?? null,
        ]);

        foreach ($itemsData as $itemData) {
            $productId = $itemData['product_id'] ?? null;
            $product = $productId ? Product::find($productId) : null;

            $itemName = $itemData['item_name'] ?? ($product?->name ?? 'Produk');
            $sku = $itemData['sku'] ?? ($product?->code ?? null);
            $unitId = $itemData['unit_id'] ?? ($product?->output_unit_id);
            $unitPrice = (float) $itemData['unit_price'];
            $unitHpp = isset($itemData['unit_hpp']) ? (float) $itemData['unit_hpp'] : (float) ($product?->base_cost ?? 0.0);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $productId,
                'item_name' => $itemName,
                'sku' => $sku,
                'description' => $itemData['description'] ?? null,
                'quantity' => (float) $itemData['quantity'],
                'unit_id' => $unitId,
                'unit_price' => $unitPrice,
                'unit_hpp' => $unitHpp,
            ]);
        }

        $invoice->recalculateTotals();

        return $invoice->fresh(['items', 'customer']);
    }

    /**
     * Convert an approved/confirmed Customer Purchase Order into an Invoice.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createFromPurchaseOrder(PurchaseOrder $po, array $attributes = []): Invoice
    {
        if ($po->po_type !== PurchaseOrder::TYPE_CUSTOMER) {
            throw new InvalidArgumentException('Only customer purchase orders can be converted to sales invoices.');
        }

        if (! $po->customer) {
            throw new InvalidArgumentException('Purchase Order does not have an associated customer.');
        }

        $business = $po->business;
        $customer = $po->customer;

        $itemsData = [];
        foreach ($po->items as $item) {
            $itemsData[] = [
                'product_id' => $item->product_id,
                'item_name' => $item->item_name,
                'sku' => $item->sku,
                'description' => $item->notes,
                'quantity' => $item->quantity,
                'unit_id' => $item->unit_id,
                'unit_price' => $item->unit_price,
                'unit_hpp' => $item->cost_price_snapshot,
            ];
        }

        $invoiceAttributes = array_merge([
            'purchase_order_id' => $po->id,
            'discount_type' => $po->discount_type,
            'discount_value' => $po->discount_value,
            'tax_percentage' => $po->tax_percentage,
            'notes' => "Diterbitkan dari PO: {$po->po_number}" . ($po->notes ? " — {$po->notes}" : ''),
            'terms_conditions' => $po->terms_and_conditions,
        ], $attributes);

        $invoice = $this->createFromProducts($business, $customer, $itemsData, $invoiceAttributes);

        // Update PO status to fully invoiced
        $po->update([
            'status' => PurchaseOrder::STATUS_FULLY_INVOICED,
        ]);

        foreach ($po->items as $item) {
            $item->update([
                'invoiced_quantity' => $item->quantity,
            ]);
        }

        return $invoice;
    }

    /**
     * Record payment receipt for an invoice.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function recordPayment(
        Invoice $invoice,
        float $amount,
        string $paymentMethod = InvoicePayment::METHOD_BANK_TRANSFER,
        array $attributes = []
    ): InvoicePayment {
        if ($amount <= 0.0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $business = $invoice->business;
        $paymentNumber = $attributes['payment_number'] ?? $this->numberGenerator->generatePaymentNumber($business);
        $paymentDate = ! empty($attributes['payment_date']) ? Carbon::parse($attributes['payment_date']) : Carbon::today();

        /** @var InvoicePayment $payment */
        $payment = InvoicePayment::create([
            'business_id' => $business->id,
            'invoice_id' => $invoice->id,
            'payment_number' => $paymentNumber,
            'payment_date' => $paymentDate->toDateString(),
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'reference_number' => $attributes['reference_number'] ?? null,
            'notes' => $attributes['notes'] ?? null,
            'receipt_file_path' => $attributes['receipt_file_path'] ?? null,
            'created_by' => $attributes['created_by'] ?? null,
        ]);

        $invoice->recalculateTotals();

        return $payment;
    }
}
