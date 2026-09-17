<?php

declare(strict_types=1);

namespace App\Jobs\WhatsApp;

use App\Domain\WhatsApp\CloudApi\WhatsAppClient;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppMessageLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class ProcessWhatsAppIncomingMessageJob
 *
 * Menangani pesan masuk dari pelanggan WhatsApp untuk tenant tertentu.
 * Menyimpan pesan ke WhatsAppMessageLog, mengupdate waktu interaksi,
 * dan menandai pesan telah dibaca (opsional).
 */
class ProcessWhatsAppIncomingMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public WhatsAppAccount $account,
        public string $businessId,
        public array $messageData,
        public string $senderName = 'Pelanggan'
    ) {}

    public function handle(): void
    {
        $messageId   = (string) ($this->messageData['id'] ?? '');
        $fromNumber  = (string) ($this->messageData['from'] ?? '');
        $messageType = (string) ($this->messageData['type'] ?? 'text');
        $timestamp   = isset($this->messageData['timestamp'])
            ? \Carbon\Carbon::createFromTimestamp((int) $this->messageData['timestamp'])
            : now();

        // Ekstraksi konten pesan berdasarkan tipe
        $extractedText = match ($messageType) {
            'text'        => (string) ($this->messageData['text']['body'] ?? ''),
            'button'      => (string) ($this->messageData['button']['text'] ?? ''),
            'interactive' => (string) ($this->messageData['interactive']['button_reply']['title']
                ?? $this->messageData['interactive']['list_reply']['title']
                ?? '[Pilihan Menu Interaktif]'),
            'image'       => '[Gambar' . (isset($this->messageData['image']['caption']) ? ': ' . $this->messageData['image']['caption'] : '') . ']',
            'document'    => '[Dokumen: ' . ($this->messageData['document']['filename'] ?? 'file') . ']',
            'location'    => '[Lokasi: Lat ' . ($this->messageData['location']['latitude'] ?? '') . ', Lng ' . ($this->messageData['location']['longitude'] ?? '') . ']',
            default       => "[Pesan {$messageType}]",
        };

        // Simpan log pesan masuk ke tabel whatsapp_message_logs
        WhatsAppMessageLog::create([
            'business_id'     => $this->businessId,
            'type'            => 'incoming',
            'recipient_phone' => $fromNumber,
            'recipient_name'  => $this->senderName,
            'message'         => $extractedText,
            'status'          => 'received',
            'error_message'   => null,
            'created_at'      => $timestamp,
        ]);

        // Auto mark as read jika diizinkan di preferensi settings merchant
        if ($this->account->getSetting('auto_mark_as_read', true) && ! empty($messageId)) {
            try {
                $client = WhatsAppClient::forAccount($this->account);
                $client->markMessageAsRead($messageId);
            } catch (\Throwable $e) {
                Log::channel('daily')->warning("[ProcessWhatsAppIncomingMessageJob] Gagal mark as read: {$e->getMessage()}");
            }
        }

        Log::channel('daily')->info("[ProcessWhatsAppIncomingMessageJob] Pesan masuk dari {$fromNumber} berhasil diproses untuk bisnis {$this->businessId}");
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return ['whatsapp', 'incoming', 'biz:' . $this->businessId];
    }
}
