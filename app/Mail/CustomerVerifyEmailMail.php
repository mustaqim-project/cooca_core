<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\GlobalCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerVerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly GlobalCustomer $customer,
        public readonly string $verificationUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[COOCA] Verifikasi Alamat Email Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-verify-email',
        );
    }
}
