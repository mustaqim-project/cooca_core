<?php

declare(strict_types=1);

namespace App\Jobs\WhatsApp;

use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Models\Business;
use App\Models\WhatsAppBroadcastCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class SendWhatsAppBroadcastJob
 *
 * Job antrean asinkron untuk mendistribusikan blast promosi massal WhatsApp
 * ke seluruh kontak pelanggan tertarget tanpa memblokir request HTTP UI kasir/owner.
 */
class SendWhatsAppBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal jika terjadi failure.
     */
    public int $tries = 1;

    /**
     * Timeout job dalam detik (diberi waktu cukup untuk antrean pesan ber-throttle).
     */
    public int $timeout = 600;

    public function __construct(
        public string $businessId,
        public string $campaignId
    ) {}

    /**
     * Eksekusi background broadcast dispatch.
     */
    public function handle(WhatsAppGatewayService $gateway): void
    {
        $business = Business::find($this->businessId);
        $campaign = WhatsAppBroadcastCampaign::find($this->campaignId);

        if (! $business || ! $campaign) {
            Log::channel('daily')->warning("[SendWhatsAppBroadcastJob] Business {$this->businessId} atau Campaign {$this->campaignId} tidak ditemukan.");
            return;
        }

        try {
            Log::channel('daily')->info("[SendWhatsAppBroadcastJob] Memulai pengiriman kampanye #{$campaign->id} ({$campaign->title}) untuk bisnis {$business->id}");
            $gateway->sendBroadcast($business, $campaign);
            Log::channel('daily')->info("[SendWhatsAppBroadcastJob] Selesai pengiriman kampanye #{$campaign->id}. Sukses: {$campaign->total_sent}, Gagal: {$campaign->total_failed}");
        } catch (\Throwable $e) {
            Log::channel('daily')->error("[SendWhatsAppBroadcastJob] Gagal memproses blast kampanye #{$campaign->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $campaign->update(['status' => 'failed']);

            throw $e;
        }
    }

    /**
     * Tags untuk monitoring antrean Laravel Queue / Horizon.
     *
     * @return list<string>
     */
    public function tags(): array
    {
        return ['whatsapp', 'broadcast', 'business:' . $this->businessId, 'campaign:' . $this->campaignId];
    }
}
