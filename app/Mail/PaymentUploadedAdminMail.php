<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\SubscriptionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentUploadedAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly SubscriptionPayment $payment
    ) {}

    public function envelope(): Envelope
    {
        $businessName = $this->payment->business?->name ?? 'Bisnis';
        $amount = number_format((float) $this->payment->amount, 0, ',', '.');

        return new Envelope(
            subject: "[Pemberitahuan Pembayaran Baru] {$businessName} - Rp {$amount}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-uploaded-admin',
        );
    }
}
