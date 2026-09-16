<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWhatsAppCloudDriver
{
    public function __construct(
        protected ?string $defaultToken = null,
        protected ?string $defaultPhoneNumberId = null,
        protected ?string $defaultWabaId = null,
        protected string $version = 'v20.0'
    ) {
        $this->defaultToken = $defaultToken ?? (string) config('services.meta_whatsapp.token', '');
        $this->defaultPhoneNumberId = $defaultPhoneNumberId ?? (string) config('services.meta_whatsapp.phone_number_id', '');
        $this->defaultWabaId = $defaultWabaId ?? (string) config('services.meta_whatsapp.waba_id', '');
        $this->version = config('services.meta_whatsapp.version', 'v20.0');
    }

    /**
     * Normalize phone number to international format without plus (e.g. 6281234567890).
     */
    public function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleaned, '0')) {
            return '62' . substr($cleaned, 1);
        }
        return $cleaned;
    }

    /**
     * Send official Authentication OTP message via Meta WhatsApp Cloud API.
     */
    public function sendOtp(
        string $phone,
        string $otpCode,
        ?string $templateName = null,
        ?string $token = null,
        ?string $phoneNumberId = null
    ): array {
        $authToken = $token ?: $this->defaultToken;
        $phoneId = $phoneNumberId ?: $this->defaultPhoneNumberId;
        $template = $templateName ?: config('services.meta_whatsapp.otp_template', 'cooca_otp');
        $cleanPhone = $this->normalizePhone($phone);

        if (!$authToken || !$phoneId) {
            return [
                'success' => false,
                'error' => 'Kredensial Meta WhatsApp Cloud API (Token / Phone Number ID) belum dikonfigurasi.',
            ];
        }

        $url = "https://graph.facebook.com/{$this->version}/{$phoneId}/messages";

        try {
            $response = Http::withToken($authToken)
                ->timeout(15)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $cleanPhone,
                    'type'              => 'template',
                    'template'          => [
                        'name'     => $template,
                        'language' => ['code' => 'id'],
                        'components' => [
                            [
                                'type'       => 'body',
                                'parameters' => [
                                    ['type' => 'text', 'text' => $otpCode],
                                ],
                            ],
                            [
                                'type'       => 'button',
                                'sub_type'   => 'url',
                                'index'      => '0',
                                'parameters' => [
                                    ['type' => 'text', 'text' => $otpCode],
                                ],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('messages.0.id'),
                    'data' => $response->json(),
                ];
            }

            $errorMessage = $response->json('error.message') ?? 'HTTP Error ' . $response->status();
            Log::error("[Meta WA Cloud] sendOtp error ({$cleanPhone}): {$errorMessage}");

            return [
                'success' => false,
                'error'   => $errorMessage,
                'details' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error("[Meta WA Cloud] sendOtp exception: " . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Send direct text message (allowed within 24h conversation window).
     */
    public function sendTextMessage(
        string $phone,
        string $text,
        ?string $token = null,
        ?string $phoneNumberId = null
    ): array {
        $authToken = $token ?: $this->defaultToken;
        $phoneId = $phoneNumberId ?: $this->defaultPhoneNumberId;
        $cleanPhone = $this->normalizePhone($phone);

        if (!$authToken || !$phoneId) {
            return [
                'success' => false,
                'error' => 'Kredensial Meta WhatsApp Cloud API belum dikonfigurasi.',
            ];
        }

        $url = "https://graph.facebook.com/{$this->version}/{$phoneId}/messages";

        try {
            $response = Http::withToken($authToken)
                ->timeout(15)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $cleanPhone,
                    'type'              => 'text',
                    'text'              => [
                        'preview_url' => false,
                        'body'        => $text,
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('messages.0.id'),
                    'data' => $response->json(),
                ];
            }

            $errorMessage = $response->json('error.message') ?? 'HTTP Error ' . $response->status();
            Log::error("[Meta WA Cloud] sendTextMessage error: {$errorMessage}");

            return [
                'success' => false,
                'error'   => $errorMessage,
            ];
        } catch (\Throwable $e) {
            Log::error("[Meta WA Cloud] sendTextMessage exception: " . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify credentials by querying phone number details from Meta Graph API.
     */
    public function verifyCredentials(string $token, string $phoneNumberId): array
    {
        $url = "https://graph.facebook.com/{$this->version}/{$phoneNumberId}";

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->get($url, [
                    'fields' => 'id,verified_name,display_phone_number,quality_rating',
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error'   => $response->json('error.message') ?? 'Gagal memverifikasi kredensial Meta.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
