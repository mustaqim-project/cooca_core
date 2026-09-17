<?php

declare(strict_types=1);

namespace App\Jobs\WhatsApp;

use App\Domain\WhatsApp\CloudApi\WhatsAppWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class ProcessWhatsAppWebhookJob
 *
 * Job antrean Redis untuk memproses payload webhook Meta secara asinkron,
 * sehingga Webhook Controller dapat mengembalikan HTTP 200 dalam waktu <200ms
 * sesuai SLA Meta Cloud API.
 */
class ProcessWhatsAppWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal jika terjadi exception.
     */
    public int $tries = 3;

    /**
     * Waktu timeout job dalam detik.
     */
    public int $timeout = 60;

    /**
     * @param array $payload Raw JSON payload dari Webhook Meta
     */
    public function __construct(
        public array $payload
    ) {}

    /**
     * Eksekusi job pemrosesan webhook.
     */
    public function handle(WhatsAppWebhookService $webhookService): void
    {
        try {
            $result = $webhookService->parseAndDispatch($this->payload);

            Log::channel('daily')->info('[ProcessWhatsAppWebhookJob] Webhook payload berhasil diproses.', $result);
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[ProcessWhatsAppWebhookJob] Gagal memproses webhook payload: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Tags untuk monitoring queue (Horizon/Redis).
     *
     * @return list<string>
     */
    public function tags(): array
    {
        return ['whatsapp', 'webhook', 'meta'];
    }
}
