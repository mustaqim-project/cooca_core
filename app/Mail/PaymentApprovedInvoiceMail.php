<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\SubscriptionPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
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
        $packageName = $this->payment->billingPackage?->name ?? ($this->payment->package_name ?? 'Layanan Cooca');
        $invoiceNo = 'INV-' . strtoupper(substr($this->payment->id, 0, 8));

        return new Envelope(
            subject: "[Kwitansi Resmi & Invoice] Pembayaran Terverifikasi: {$packageName} ({$invoiceNo})",
        );
    }

    public function content(): Content
    {
        $invoiceNo = 'INV-' . strtoupper(substr($this->payment->id, 0, 8));

        return new Content(
            view: 'emails.payment-approved-invoice',
            with: [
                'invoiceNo' => $invoiceNo,
            ],
        );
    }

    /**
     * Attach the official PDF invoice & receipt to the email.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $invoiceNo = 'INV-' . strtoupper(substr($this->payment->id, 0, 8));
        $pdfContent = $this->generatePdfContent($invoiceNo);

        return [
            Attachment::fromData(fn () => $pdfContent, "Invoice-Cooca-{$invoiceNo}.pdf")
                ->withMime('application/pdf'),
        ];
    }

    /**
     * Generate binary PDF string for the official invoice & receipt.
     */
    public function generatePdfContent(string $invoiceNo): string
    {
        $payment = $this->payment;
        $payment->loadMissing(['business', 'billingPackage', 'user']);

        $methodDetails = $payment->getPaymentMethodDetails();
        $paymentMethodName = $methodDetails['name'] ?? strtoupper((string) ($payment->payment_method ?? 'QRIS'));

        $rawTerbilang = self::formatTerbilang((int) $payment->amount);
        $terbilang = trim(preg_replace('/\s+/', ' ', $rawTerbilang));

        $pdf = Pdf::loadView('pdf.subscription_invoice_pdf', [
            'payment' => $payment,
            'invoiceNo' => $invoiceNo,
            'paymentMethodName' => $paymentMethodName,
            'terbilang' => $terbilang,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Konversi nominal angka ke format terbilang resmi bahasa Indonesia.
     */
    public static function formatTerbilang(int|float $nilai): string
    {
        $nilai = abs((int) $nilai);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($nilai < 12) {
            return $huruf[$nilai];
        }

        if ($nilai < 20) {
            return self::formatTerbilang($nilai - 10) . ' Belas';
        }

        if ($nilai < 100) {
            return self::formatTerbilang((int) ($nilai / 10)) . ' Puluh ' . self::formatTerbilang($nilai % 10);
        }

        if ($nilai < 200) {
            return 'Seratus ' . self::formatTerbilang($nilai - 100);
        }

        if ($nilai < 1000) {
            return self::formatTerbilang((int) ($nilai / 100)) . ' Ratus ' . self::formatTerbilang($nilai % 100);
        }

        if ($nilai < 2000) {
            return 'Seribu ' . self::formatTerbilang($nilai - 1000);
        }

        if ($nilai < 1000000) {
            return self::formatTerbilang((int) ($nilai / 1000)) . ' Ribu ' . self::formatTerbilang($nilai % 1000);
        }

        if ($nilai < 1000000000) {
            return self::formatTerbilang((int) ($nilai / 1000000)) . ' Juta ' . self::formatTerbilang($nilai % 1000000);
        }

        if ($nilai < 1000000000000) {
            return self::formatTerbilang((int) ($nilai / 1000000000)) . ' Miliar ' . self::formatTerbilang(fmod($nilai, 1000000000));
        }

        return self::formatTerbilang((int) ($nilai / 1000000000000)) . ' Triliun ' . self::formatTerbilang(fmod($nilai, 1000000000000));
    }
}
