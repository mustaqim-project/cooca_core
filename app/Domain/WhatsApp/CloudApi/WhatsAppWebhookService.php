<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\CloudApi;

use App\Jobs\WhatsApp\ProcessWhatsAppIncomingMessageJob;
use App\Jobs\WhatsApp\ProcessWhatsAppMessageStatusJob;
use App\Models\SystemSetting;
use App\Models\WhatsAppAccount;
use Illuminate\Support\Facades\Log;

/**
 * Class WhatsAppWebhookService
 *
 * Layanan pemrosesan webhook WhatsApp Cloud API resmi Meta:
 * - Verifikasi tanda tangan kriptografis HMAC-SHA256 (X-Hub-Signature-256).
 * - Verifikasi handshake token (GET hub.challenge).
 * - Identifikasi merchant/tenant berdasarkan phone_number_id.
 * - Distribusi payload event pesan masuk & status delivery ke antrean Job Redis.
 */
class WhatsAppWebhookService
{
    /**
     * Verifikasi integritas tanda tangan HMAC-SHA256 dari Meta.
     *
     * @param string $rawPayload Konten mentah body HTTP request ($request->getContent())
     * @param string|null $signatureHeader Nilai header 'X-Hub-Signature-256'
     */
    public function verifyWebhookSignature(string $rawPayload, ?string $signatureHeader): bool
    {
        $appSecret = (string) (SystemSetting::get('meta_wa_app_secret') ?: config('services.meta_whatsapp.app_secret', ''));

        if (empty($appSecret)) {
            Log::channel('daily')->critical('[Meta WA Webhook] META_APP_SECRET belum dikonfigurasi di environment atau sistem settings!');
            return false;
        }

        if (empty($signatureHeader) || ! str_starts_with($signatureHeader, 'sha256=')) {
            Log::channel('daily')->warning('[Meta WA Webhook] Signature header X-Hub-Signature-256 hilang atau format tidak valid.');
            return false;
        }

        $expectedHash = hash_hmac('sha256', $rawPayload, $appSecret);
        $providedHash = substr($signatureHeader, 7); // Potong prefix 'sha256='

        return hash_equals($expectedHash, $providedHash);
    }

    /**
     * Verifikasi webhook saat Meta mengirimkan GET verification challenge.
     */
    public function verifyChallenge(?string $mode, ?string $token, ?string $challenge): ?string
    {
        $expectedToken = (string) (SystemSetting::get('meta_wa_webhook_verify_token') ?: config('services.meta_whatsapp.webhook_verify_token', ''));

        if ($mode === 'subscribe' && ! empty($expectedToken) && hash_equals($expectedToken, (string) $token)) {
            Log::channel('daily')->info('[Meta WA Webhook] Webhook subscription handshake verified successfully.');
            return $challenge;
        }

        Log::channel('daily')->warning('[Meta WA Webhook] Handshake failed: invalid verify token or mode.', [
            'mode'           => $mode,
            'provided_token' => $token ? substr($token, 0, 3) . '***' : null,
        ]);

        return null;
    }

    /**
     * Temukan akun WhatsAppAccount merchant berdasarkan phone_number_id Meta.
     */
    public function resolveAccountByPhoneNumberId(string $phoneNumberId): ?WhatsAppAccount
    {
        return WhatsAppAccount::where('phone_number_id', $phoneNumberId)->first();
    }

    /**
     * Mengurai payload webhook Meta dan mendistribusikan ke Jobs asynchronous.
     *
     * @return array{messages_dispatched: int, statuses_dispatched: int, unmapped_accounts: int}
     */
    public function parseAndDispatch(array $payload): array
    {
        $messagesDispatched = 0;
        $statusesDispatched = 0;
        $unmappedAccounts   = 0;

        if (($payload['object'] ?? '') !== 'whatsapp_business_account') {
            return [
                'messages_dispatched' => 0,
                'statuses_dispatched' => 0,
                'unmapped_accounts'   => 0,
            ];
        }

        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];

            foreach ($changes as $change) {
                if (($change['field'] ?? '') !== 'messages') {
                    continue;
                }

                $value = $change['value'] ?? [];
                $metadata = $value['metadata'] ?? [];
                $phoneNumberId = (string) ($metadata['phone_number_id'] ?? '');

                if (empty($phoneNumberId)) {
                    continue;
                }

                // Identifikasi tenant merchant COOCA
                $account = $this->resolveAccountByPhoneNumberId($phoneNumberId);

                if (! $account) {
                    $unmappedAccounts++;
                    Log::channel('daily')->warning("[Meta WA Webhook] Menerima webhook untuk phone_number_id '{$phoneNumberId}' yang tidak terdaftar pada merchant mana pun.");
                    continue;
                }

                $businessId = $account->business_id;

                // 1. Tangani Pesan Masuk (Inbound Messages)
                $messages = $value['messages'] ?? [];
                $contacts = $value['contacts'] ?? [];
                $contactName = $contacts[0]['profile']['name'] ?? 'Pelanggan';

                foreach ($messages as $messageItem) {
                    ProcessWhatsAppIncomingMessageJob::dispatch(
                        account: $account,
                        businessId: $businessId,
                        messageData: $messageItem,
                        senderName: $contactName
                    )->onQueue('whatsapp');

                    $messagesDispatched++;
                }

                // 2. Tangani Status Updates (sent, delivered, read, failed)
                $statuses = $value['statuses'] ?? [];
                foreach ($statuses as $statusItem) {
                    ProcessWhatsAppMessageStatusJob::dispatch(
                        account: $account,
                        businessId: $businessId,
                        statusData: $statusItem
                    )->onQueue('whatsapp');

                    $statusesDispatched++;
                }
            }
        }

        return [
            'messages_dispatched' => $messagesDispatched,
            'statuses_dispatched' => $statusesDispatched,
            'unmapped_accounts'   => $unmappedAccounts,
        ];
    }
}
