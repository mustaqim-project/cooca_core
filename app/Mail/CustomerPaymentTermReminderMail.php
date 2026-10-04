<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerPaymentTermReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly Customer $customer,
        public readonly Business $business,
        public readonly string $reminderType = 'due_date',
        public readonly ?string $customNotes = null
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->reminderType) {
            'upcoming_h3' => "[Pengingat Tagihan] Faktur #{$this->invoice->invoice_number} Jatuh Tempo dalam 3 Hari - {$this->business->name}",
            'due_date' => "[Jatuh Tempo Hari Ini] Tagihan Faktur #{$this->invoice->invoice_number} - {$this->business->name}",
            'overdue' => "[Pemberitahuan Jatuh Tempo] Faktur #{$this->invoice->invoice_number} Belum Lunas - {$this->business->name}",
            default => "[Rincian Tagihan Faktur] #{$this->invoice->invoice_number} - {$this->business->name}",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-payment-term-reminder',
            with: [
                'invoice' => $this->invoice,
                'customer' => $this->customer,
                'business' => $this->business,
                'reminderType' => $this->reminderType,
                'customNotes' => $this->customNotes,
                'invoiceUrl' => route('invoices.show', $this->invoice->id),
            ]
        );
    }
}
