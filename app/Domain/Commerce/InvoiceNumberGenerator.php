<?php

declare(strict_types=1);

namespace App\Domain\Commerce;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PurchaseOrder;

final class InvoiceNumberGenerator
{
    /**
     * Generate unique sequential invoice number per business for current month.
     * Format: INV-YYYYMM-XXXX (e.g. INV-202608-0001)
     */
    public function generateInvoiceNumber(Business $business): string
    {
        $prefix = 'INV-' . date('Ym') . '-';

        $lastInvoice = Invoice::where('business_id', $business->id)
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('invoice_number')
            ->first();

        $nextNumber = 1;
        if ($lastInvoice) {
            $lastSuffix = (int) substr($lastInvoice->invoice_number, strlen($prefix));
            $nextNumber = $lastSuffix + 1;
        }

        return sprintf('%s%04d', $prefix, $nextNumber);
    }

    /**
     * Generate unique sequential purchase order number per business for current month.
     * Format: PO-YYYYMM-XXXX (e.g. PO-202608-0001)
     */
    public function generatePoNumber(Business $business, string $type = 'customer'): string
    {
        $prefix = ($type === 'supplier' ? 'VPO-' : 'PO-') . date('Ym') . '-';

        $lastPo = PurchaseOrder::where('business_id', $business->id)
            ->where('po_number', 'like', "{$prefix}%")
            ->orderByDesc('po_number')
            ->first();

        $nextNumber = 1;
        if ($lastPo) {
            $lastSuffix = (int) substr($lastPo->po_number, strlen($prefix));
            $nextNumber = $lastSuffix + 1;
        }

        return sprintf('%s%04d', $prefix, $nextNumber);
    }

    /**
     * Generate unique payment number per business for current month.
     * Format: PAY-YYYYMM-XXXX (e.g. PAY-202608-0001)
     */
    public function generatePaymentNumber(Business $business): string
    {
        $prefix = 'PAY-' . date('Ym') . '-';

        $lastPayment = InvoicePayment::where('business_id', $business->id)
            ->where('payment_number', 'like', "{$prefix}%")
            ->orderByDesc('payment_number')
            ->first();

        $nextNumber = 1;
        if ($lastPayment) {
            $lastSuffix = (int) substr($lastPayment->payment_number, strlen($prefix));
            $nextNumber = $lastSuffix + 1;
        }

        return sprintf('%s%04d', $prefix, $nextNumber);
    }
}
