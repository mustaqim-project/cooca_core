<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\SubscriptionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentApprovedInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly SubscriptionPayment $payment
    ) {}

    public function envelope(): Envelope
    {
        $packageName = $this->payment->billingPackage?->name ?? 'Layanan Cooca';
        $invoiceNo = 'INV-' . strtoupper(substr($this->payment->id, 0, 8));

        return new Envelope(
            subject: "[Kwitansi Resmi & Invoice] Pembayaran Terverifikasi: {$packageName} ({$invoiceNo})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-approved-invoice',
        );
    }
}
