<?php

declare(strict_types=1);

namespace App\Domain\Document;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

class PdfService
{
    /**
     * Render Invoice as printable HTML/PDF document.
     */
    public function renderInvoice(Invoice $invoice): View
    {
        $invoice->load(['business', 'customer', 'items.product', 'payments']);
        return ViewFacade::make('documents.invoice_pdf', [
            'invoice' => $invoice,
            'business' => $invoice->business,
            'customer' => $invoice->customer,
            'items' => $invoice->items,
        ]);
    }

    /**
     * Render Purchase Order as printable HTML/PDF document.
     */
    public function renderPurchaseOrder(PurchaseOrder $po): View
    {
        $po->load(['business', 'supplier', 'items']);
        return ViewFacade::make('documents.purchase_order_pdf', [
            'po' => $po,
            'business' => $po->business,
            'supplier' => $po->supplier,
            'items' => $po->items,
        ]);
    }

    /**
     * Render Quotation as printable HTML/PDF document.
     */
    public function renderQuotation(Quotation $quotation): View
    {
        $quotation->load(['business', 'customer', 'items']);
        return ViewFacade::make('documents.quotation_pdf', [
            'quotation' => $quotation,
            'business' => $quotation->business,
            'customer' => $quotation->customer,
            'items' => $quotation->items,
        ]);
    }
}
