<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWhatsAppCloudDriver
{
    protected string $baseUrl;

    public function __construct(
        protected ?string $defaultToken = null,
        protected ?string $defaultPhoneNumberId = null,
        protected ?string $defaultWabaId = null,
        protected ?string $version = null
    ) {
        $this->defaultToken = $defaultToken ?? (string) (\App\Models\SystemSetting::get('meta_wa_token') ?: config('services.meta_whatsapp.token', ''));
        $this->defaultPhoneNumberId = $defaultPhoneNumberId ?? (string) (\App\Models\SystemSetting::get('meta_wa_phone_number_id') ?: config('services.meta_whatsapp.phone_number_id', ''));
        $this->defaultWabaId = $defaultWabaId ?? (string) (\App\Models\SystemSetting::get('meta_wa_waba_id') ?: config('services.meta_whatsapp.waba_id', ''));
        $this->version = $version ?? (string) (\App\Models\SystemSetting::get('meta_wa_graph_version') ?: config('services.meta_whatsapp.version', 'v21.0'));
        $this->baseUrl = (string) (\App\Models\SystemSetting::get('meta_wa_graph_url') ?: config('services.meta_whatsapp.graph_url', 'https://graph.facebook.com'));
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

        $url = "{$this->baseUrl}/{$this->version}/{$phoneId}/messages";

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

            $errorMessage = $this->formatErrorMessage($response);
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

        $url = "{$this->baseUrl}/{$this->version}/{$phoneId}/messages";

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

            $errorMessage = $this->formatErrorMessage($response);
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
     * Format user-friendly error message from Meta response.
     */
    protected function formatErrorMessage(\Illuminate\Http\Client\Response $response): string
    {
        $errorCode = (int) $response->json('error.code');
        $rawMessage = (string) ($response->json('error.message') ?? ('HTTP Error ' . $response->status()));

        if ($errorCode === 133010) {
            $wabaParam = $this->defaultWabaId ? "?waba_id={$this->defaultWabaId}" : '';
            return "Nomor WhatsApp belum terdaftar/terverifikasi di Meta Cloud API (#133010: Account not registered). Status nomor masih DISCONNECTED / NOT_VERIFIED. Silakan selesaikan verifikasi nomor di Meta WhatsApp Manager: https://business.facebook.com/wa/manage/phone-numbers/{$wabaParam}";
        }

        return $rawMessage;
    }

    /**
     * Verify credentials by querying phone number details from Meta Graph API.
     */
    public function verifyCredentials(string $token, string $phoneNumberId): array
    {
        $url = "{$this->baseUrl}/{$this->version}/{$phoneNumberId}";

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->get($url, [
                    'fields' => 'id,verified_name,display_phone_number,quality_rating,code_verification_status,status,platform_type',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $isCodeVerified = strtoupper((string) ($data['code_verification_status'] ?? '')) === 'VERIFIED';
                $isStatusConnected = strtoupper((string) ($data['status'] ?? '')) === 'CONNECTED';
                $isFullyConnected = $isCodeVerified && $isStatusConnected;

                return [
                    'success'                  => true,
                    'is_connected'             => $isFullyConnected,
                    'status'                   => $data['status'] ?? 'DISCONNECTED',
                    'code_verification_status' => $data['code_verification_status'] ?? 'NOT_VERIFIED',
                    'platform_type'            => $data['platform_type'] ?? 'UNKNOWN',
                    'data'                     => $data,
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
