<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send Invoice link or notification to customer via WhatsApp.
     */
    public function sendInvoiceNotification(Invoice $invoice, Business $business, ?Customer $customer = null): bool
    {
        $phone = $customer?->phone ?? $invoice->customer_phone;
        if (!$phone) {
            return false;
        }

        $formattedPhone = $this->formatPhoneNumber($phone);
        $totalFormatted = 'Rp ' . number_format((float)$invoice->total_amount, 0, ',', '.');
        $invoiceUrl = route('invoices.show', $invoice->id);

        $message = "Halo *{$customer?->name}*,\n\n"
            . "Berikut adalah rincian tagihan faktur dari *{$business->name}*:\n"
            . "• No. Faktur: *#{$invoice->invoice_number}*\n"
            . "• Total Tagihan: *{$totalFormatted}*\n"
            . "• Jatuh Tempo: *" . ($invoice->due_date ?? '-') . "*\n\n"
            . "Lihat & unduh dokumen faktur Anda di sini:\n{$invoiceUrl}\n\n"
            . "Terima kasih atas kerja sama Anda.\n_{$business->name}_";

        return $this->sendMessage($formattedPhone, $message);
    }

    /**
     * Send general text message via configured gateway.
     */
    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $apiKey = config('services.whatsapp.api_key');
            $endpoint = config('services.whatsapp.endpoint', 'https://api.fonnte.com/send');

            if (!$apiKey) {
                // Log mock notification if API key not set
                Log::info("WhatsApp (Mock Dispatch) to {$phone}: {$message}");
                return true;
            }

            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->post($endpoint, [
                'target' => $phone,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("WhatsApp Notification Failed: " . $e->getMessage());
            return false;
        }
    }

    private function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleaned, '0')) {
            return '62' . substr($cleaned, 1);
        }
        return $cleaned;
    }
}
