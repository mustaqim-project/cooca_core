<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\BusinessSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly BusinessSubscription $subscription,
        public readonly int $daysRemaining
    ) {}

    public function envelope(): Envelope
    {
        $businessName = $this->subscription->business?->name ?? 'Bisnis';
        $dayText = match ($this->daysRemaining) {
            1 => 'Besok (H-1)',
            3 => '3 Hari Lagi (H-3)',
            7 => '7 Hari Lagi (H-7)',
            default => "{$this->daysRemaining} Hari Lagi",
        };

        return new Envelope(
            subject: "[Penting] Langganan Cooca {$businessName} Berakhir dalam {$dayText} — Perpanjang Sekarang",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.plan-expiry-reminder',
        );
    }
}
