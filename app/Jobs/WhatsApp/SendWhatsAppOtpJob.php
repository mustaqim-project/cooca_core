<?php

declare(strict_types=1);

namespace App\Jobs\WhatsApp;

use App\Domain\WhatsApp\AdminWhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job: SendWhatsAppOtpJob
 *
 * Mengirim kode OTP autentikasi resmi via Meta WhatsApp Cloud API secara asynchronous.
 * Didispatch ke antrean 'whatsapp' sehingga user tidak menunggu respons API Meta.
 *
 * Retry policy: 2 kali dengan backoff eksponensial (3s, 6s).
 * Timeout: 30 detik per attempt.
 */
class SendWhatsAppOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah maksimum percobaan pengiriman.
     */
    public int $tries = 2;

    /**
     * Waktu tunggu eksponensial antar percobaan (detik).
     *
     * @var array<int, int>
     */
    public array $backoff = [3, 6];

    /**
     * Batas waktu eksekusi per attempt (detik).
     */
    public int $timeout = 30;

    public function __construct(
        public readonly string $phone,
        public readonly string $otpCode,
        public readonly ?string $templateName = null
    ) {
        $this->onQueue('whatsapp');
    }

    /**
     * Eksekusi pengiriman OTP via AdminWhatsAppService.
     */
    public function handle(AdminWhatsAppService $adminWa): void
    {
        $maskedPhone = substr($this->phone, 0, 4) . '****' . substr($this->phone, -3);

        Log::channel('daily')->info("[SendWhatsAppOtpJob] Mengirim OTP ke {$maskedPhone}...");

        $result = $adminWa->sendOtp($this->phone, $this->otpCode);

        if ($result['success'] ?? false) {
            $messageId = $result['message_id'] ?? 'unknown';
            Log::channel('daily')->info("[SendWhatsAppOtpJob] OTP berhasil dikirim ke {$maskedPhone}. message_id: {$messageId}");
        } else {
            $error = $result['error'] ?? 'Unknown error';
            Log::channel('daily')->error("[SendWhatsAppOtpJob] Gagal mengirim OTP ke {$maskedPhone}: {$error}");

            // Lempar exception agar Job bisa di-retry
            throw new \RuntimeException("WhatsApp OTP gagal dikirim ke {$maskedPhone}: {$error}");
        }
    }

    /**
     * Handle kegagalan setelah semua retry habis.
     */
    public function failed(?\Throwable $exception): void
    {
        $maskedPhone = substr($this->phone, 0, 4) . '****' . substr($this->phone, -3);

        Log::channel('daily')->critical(
            "[SendWhatsAppOtpJob] FINAL FAILURE - OTP untuk {$maskedPhone} gagal dikirim setelah {$this->tries} percobaan.",
            ['error' => $exception?->getMessage()]
        );
    }
}
