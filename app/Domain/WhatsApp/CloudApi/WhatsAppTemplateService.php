<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\CloudApi;

use App\Domain\Pos\PosReceiptImageService;
use App\Domain\WhatsApp\CloudApi\Templates\InvoiceTemplate;
use App\Domain\WhatsApp\CloudApi\Templates\PosReceiptTemplate;
use App\Models\Business;
use App\Models\PosOrder;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppMessageLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Class WhatsAppTemplateService
 *
 * Orkestrator pengiriman pesan template resmi WhatsApp Cloud API (Meta)
 * untuk transaksi bisnis: Struk Kasir POS dan Faktur Penjualan (Invoice).
 * Menyediakan proteksi anti-duplicate, atomic cache lock, dan logging terisolasi.
 */
class WhatsAppTemplateService
{
    /**
     * Kirim struk kasir POS resmi menggunakan template Meta.
     */
    public function sendPosReceipt(PosOrder $order, ?string $customPhone = null, bool $force = false): array
    {
        $business = $order->business;
        $phone    = $customPhone ?? $order->customer?->phone ?? $order->customer_phone_guest ?? '';

        if (empty($phone)) {
            return [
                'success' => false,
                'error'   => 'Nomor telepon pelanggan tidak tersedia.',
            ];
        }

        // 1. Temukan akun WhatsApp Cloud API aktif milik merchant ini
        $account = WhatsAppAccount::where('business_id', $business->id)
            ->where('status', 'active')
            ->first();

        if (! $account || ! $account->isActive()) {
            return [
                'success' => false,
                'error'   => 'Akun resmi Meta WhatsApp Cloud API untuk bisnis ini belum terhubung atau token kedaluwarsa.',
            ];
        }

        // 2. Proteksi Double-Click: Atomic lock
        $lockKey = "meta_wa_receipt_lock_{$order->id}";
        if (! Cache::add($lockKey, true, 15)) {
            return [
                'success' => true,
                'message' => 'Pengiriman struk sedang diproses di antrean.',
            ];
        }

        try {
            // 3. Proteksi Pengiriman Duplikat dalam 60 detik
            if (! $force) {
                $recentlySent = WhatsAppMessageLog::where('order_id', $order->id)
                    ->where('type', 'receipt')
                    ->where('status', 'sent')
                    ->where('created_at', '>=', now()->subSeconds(60))
                    ->exists();

                if ($recentlySent) {
                    return [
                        'success' => true,
                        'message' => 'Struk pesanan ini sudah berhasil dikirim beberapa saat lalu.',
                    ];
                }
            }

            // 4. Generate gambar struk digital (jika service tersedia)
            $receiptImageUrl = null;
            try {
                if (class_exists(PosReceiptImageService::class)) {
                    /** @var PosReceiptImageService $imageService */
                    $imageService = app(PosReceiptImageService::class);
                    $relativePath = $imageService->generateAndStore($order);
                    $receiptImageUrl = asset('storage/' . $relativePath);
                }
            } catch (\Throwable $e) {
                Log::channel('daily')->warning("[WhatsAppTemplateService] Gagal generate struk image: {$e->getMessage()}");
            }

            // 5. Susun template builder
            $templateName = (string) $account->getSetting('receipt_template_name', PosReceiptTemplate::DEFAULT_TEMPLATE_NAME);
            $templateBuilder = PosReceiptTemplate::build($order, $receiptImageUrl, $templateName);

            // 6. Eksekusi pengiriman melalui WhatsAppClient
            $client = WhatsAppClient::forAccount($account);
            $result = $client->sendTemplateMessage(
                to: $phone,
                templateName: $templateBuilder->getTemplateName(),
                languageCode: $templateBuilder->getLanguageCode(),
                components: $templateBuilder->buildComponents()
            );

            $isSuccess = (bool) ($result['success'] ?? false);
            $messageId = $result['message_id'] ?? null;
            $errorMsg  = $result['error'] ?? null;

            // 7. Simpan log transaksi pengiriman
            WhatsAppMessageLog::create([
                'business_id'     => $business->id,
                'type'            => 'receipt',
                'recipient_phone' => $client->normalizePhone($phone),
                'recipient_name'  => $order->customer?->name ?? $order->customer_name_guest ?? 'Pelanggan',
                'message'         => "[Template: {$templateName}] Struk Pembelian #{$order->order_number}",
                'status'          => $isSuccess ? 'sent' : 'failed',
                'order_id'        => $order->id,
                'error_message'   => $errorMsg,
            ]);

            return $result;
        } finally {
            Cache::forget($lockKey);
        }
    }

    /**
     * Kirim faktur / invoice resmi menggunakan template Meta.
     */
    public function sendInvoice(
        Business $business,
        string $recipientPhone,
        string $customerName,
        string $invoiceNumber,
        \DateTimeInterface $dueDate,
        float|int $totalAmount,
        ?string $invoicePdfUrl = null,
        ?string $invoiceUrlSuffix = null
    ): array {
        $account = WhatsAppAccount::where('business_id', $business->id)
            ->where('status', 'active')
            ->first();

        if (! $account || ! $account->isActive()) {
            return [
                'success' => false,
                'error'   => 'Akun resmi Meta WhatsApp Cloud API belum terhubung.',
            ];
        }

        $templateName = (string) $account->getSetting('invoice_template_name', InvoiceTemplate::DEFAULT_TEMPLATE_NAME);
        $templateBuilder = InvoiceTemplate::build(
            customerName: $customerName,
            invoiceNumber: $invoiceNumber,
            dueDate: $dueDate,
            totalAmount: $totalAmount,
            storeName: $business->name,
            invoicePdfUrl: $invoicePdfUrl,
            invoiceUrlSuffix: $invoiceUrlSuffix,
            currency: $business->currency ?? 'IDR',
            templateName: $templateName
        );

        $client = WhatsAppClient::forAccount($account);
        $result = $client->sendTemplateMessage(
            to: $recipientPhone,
            templateName: $templateBuilder->getTemplateName(),
            languageCode: $templateBuilder->getLanguageCode(),
            components: $templateBuilder->buildComponents()
        );

        $isSuccess = (bool) ($result['success'] ?? false);

        WhatsAppMessageLog::create([
            'business_id'     => $business->id,
            'type'            => 'invoice',
            'recipient_phone' => $client->normalizePhone($recipientPhone),
            'recipient_name'  => $customerName,
            'message'         => "[Template: {$templateName}] Invoice #{$invoiceNumber}",
            'status'          => $isSuccess ? 'sent' : 'failed',
            'error_message'   => $result['error'] ?? null,
        ]);

        return $result;
    }
}
