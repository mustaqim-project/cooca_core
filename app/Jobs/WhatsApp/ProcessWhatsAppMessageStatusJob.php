<?php

declare(strict_types=1);

namespace App\Jobs\WhatsApp;

use App\Models\WhatsAppAccount;
use App\Models\WhatsAppMessageLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class ProcessWhatsAppMessageStatusJob
 *
 * Menangani pembaruan status pengiriman pesan dari Meta:
 * - 'sent': Pesan berhasil diterima server Meta.
 * - 'delivered': Pesan berhasil terkirim ke perangkat penerima.
 * - 'read': Pesan telah dibaca oleh penerima.
 * - 'failed': Pengiriman gagal (misal: nomor tidak terdaftar, di luar 24h window, rate limit).
 */
class ProcessWhatsAppMessageStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public WhatsAppAccount $account,
        public string $businessId,
        public array $statusData
    ) {}

    public function handle(): void
    {
        $status          = (string) ($this->statusData['status'] ?? '');
        $messageId       = (string) ($this->statusData['id'] ?? '');
        $recipientId     = (string) ($this->statusData['recipient_id'] ?? '');
        $errors          = $this->statusData['errors'] ?? [];

        $errorMessage = null;
        if (! empty($errors)) {
            $firstErr = $errors[0] ?? [];
            $code     = $firstErr['code'] ?? 'UNKNOWN';
            $title    = $firstErr['title'] ?? 'Error';
            $desc     = $firstErr['error_data']['details'] ?? ($firstErr['message'] ?? '');
            $errorMessage = "[Meta Error {$code}] {$title}: {$desc}";
        }

        // Cari log pesan terakhir yang cocok untuk penerima ini di bisnis yang bersangkutan
        $log = WhatsAppMessageLog::where('business_id', $this->businessId)
            ->where('recipient_phone', $recipientId)
            ->latest('id')
            ->first();

        if ($log) {
            $updateData = ['status' => $status];
            if ($errorMessage !== null) {
                $updateData['error_message'] = $errorMessage;
            }

            $log->update($updateData);

            Log::channel('daily')->info("[ProcessWhatsAppMessageStatusJob] Status log pesan #{$log->id} diperbarui menjadi '{$status}'", [
                'business_id' => $this->businessId,
                'message_id'  => $messageId,
                'status'      => $status,
            ]);
        }
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return ['whatsapp', 'status', 'biz:' . $this->businessId];
    }
}
