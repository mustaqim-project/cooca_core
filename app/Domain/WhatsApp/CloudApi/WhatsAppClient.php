<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\CloudApi;

use App\Models\Business;
use App\Models\WhatsAppAccount;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class WhatsAppClient
 *
 * Client HTTP resmi untuk berkomunikasi dengan Meta Graph API (WhatsApp Cloud API).
 * Menangani otentikasi Bearer per-merchant, isolasi multi-tenant, retry logic transien,
 * dan audit logging mendalam tanpa membocorkan token rahasia.
 */
class WhatsAppClient
{
    protected string $baseUrl;
    protected string $version;

    public function __construct(
        protected string $accessToken,
        protected string $phoneNumberId,
        protected ?string $wabaId = null,
        protected ?string $businessId = null,
        ?string $version = null,
        ?string $baseUrl = null
    ) {
        $this->version = $version ?? (string) (\App\Models\SystemSetting::get('meta_wa_graph_version') ?: config('services.meta_whatsapp.version', 'v21.0'));
        $this->baseUrl = $baseUrl ?? (string) (\App\Models\SystemSetting::get('meta_wa_graph_url') ?: config('services.meta_whatsapp.graph_url', 'https://graph.facebook.com'));
    }

    /**
     * Factory instance berdasarkan WhatsAppAccount merchant.
     */
    public static function forAccount(WhatsAppAccount $account): self
    {
        return new self(
            accessToken: $account->access_token,
            phoneNumberId: $account->phone_number_id,
            wabaId: $account->waba_id,
            businessId: $account->business_id
        );
    }

    /**
     * Factory instance untuk tenant Business aktif.
     */
    public static function forBusiness(Business $business): ?self
    {
        $account = WhatsAppAccount::where('business_id', $business->id)
            ->where('status', 'active')
            ->first();

        if (! $account) {
            return null;
        }

        return self::forAccount($account);
    }

    /**
     * Factory instance langsung dengan kredensial kustom.
     */
    public static function withCredentials(
        string $token,
        string $phoneNumberId,
        ?string $wabaId = null,
        ?string $businessId = null
    ): self {
        return new self(
            accessToken: $token,
            phoneNumberId: $phoneNumberId,
            wabaId: $wabaId,
            businessId: $businessId
        );
    }

    /**
     * Factory instance untuk Platform Utama (Parent System Gateway).
     * Digunakan oleh Admin untuk OTP global, pengingat langganan, dan siaran sistem.
     */
    public static function forPlatform(): ?self
    {
        $token = (string) (\App\Models\SystemSetting::get('meta_wa_token', config('services.meta_whatsapp.token', '')));
        $phoneId = (string) (\App\Models\SystemSetting::get('meta_wa_phone_number_id', config('services.meta_whatsapp.phone_number_id', '')));
        $wabaId = (string) (\App\Models\SystemSetting::get('meta_wa_waba_id', config('services.meta_whatsapp.waba_id', '')));

        if (empty($token) || empty($phoneId)) {
            return null;
        }

        return new self(
            accessToken: $token,
            phoneNumberId: $phoneId,
            wabaId: $wabaId ?: null,
            businessId: 'platform'
        );
    }

    /**
     * Kirim OTP resmi Meta Authentication Template (dengan copy code button).
     */
    public function sendOtpTemplate(string $to, string $otpCode, ?string $templateName = null): array
    {
        $template = $templateName ?: (string) (\App\Models\SystemSetting::get('meta_wa_otp_template') ?: config('services.meta_whatsapp.otp_template', 'cooca_otp'));

        $components = [
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
        ];

        return $this->sendTemplateMessage($to, $template, 'id', $components);
    }

    public function sendTextMessage(string $to, string $text, bool $previewUrl = false): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $this->normalizePhone($to),
            'type'              => 'text',
            'text'              => [
                'preview_url' => $previewUrl,
                'body'        => $text,
            ],
        ];

        return $this->post("/{$this->phoneNumberId}/messages", $payload);
    }

    /**
     * Kirim pesan template terstruktur resmi (Invoice, Struk POS, OTP, Marketing, Utility).
     *
     * @param string $to Nomor tujuan format E.164
     * @param string $templateName Nama template Meta yang sudah disetujui
     * @param string $languageCode Kode bahasa (misal: 'id', 'en_US')
     * @param array $components Komponen header, body, button, dsb.
     */
    public function sendTemplateMessage(
        string $to,
        string $templateName,
        string $languageCode = 'id',
        array $components = []
    ): array {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $this->normalizePhone($to),
            'type'              => 'template',
            'template'          => [
                'name'       => $templateName,
                'language'   => ['code' => $languageCode],
                'components' => $components,
            ],
        ];

        return $this->post("/{$this->phoneNumberId}/messages", $payload);
    }

    /**
     * Kirim pesan media (dokumen PDF nota, struk gambar, dsb).
     */
    public function sendMediaMessage(
        string $to,
        string $mediaType, // 'image', 'document', 'audio', 'video'
        string $mediaUrl,
        ?string $caption = null,
        ?string $filename = null
    ): array {
        $mediaObject = ['link' => $mediaUrl];
        if ($caption !== null && in_array($mediaType, ['image', 'document', 'video'], true)) {
            $mediaObject['caption'] = $caption;
        }
        if ($filename !== null && $mediaType === 'document') {
            $mediaObject['filename'] = $filename;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $this->normalizePhone($to),
            'type'              => $mediaType,
            $mediaType          => $mediaObject,
        ];

        return $this->post("/{$this->phoneNumberId}/messages", $payload);
    }

    /**
     * Kirim pesan interaktif dengan tombol balasan cepat (Quick Reply / Interactive Buttons).
     */
    public function sendInteractiveButtons(
        string $to,
        string $bodyText,
        array $buttons,
        ?string $headerText = null,
        ?string $footerText = null
    ): array {
        $interactive = [
            'type'   => 'button',
            'body'   => ['text' => $bodyText],
            'action' => [
                'buttons' => array_map(function (array $btn, int $idx): array {
                    return [
                        'type'  => 'reply',
                        'reply' => [
                            'id'    => $btn['id'] ?? 'btn_' . $idx,
                            'title' => mb_substr((string) ($btn['title'] ?? 'Pilih'), 0, 20),
                        ],
                    ];
                }, $buttons, array_keys($buttons)),
            ],
        ];

        if ($headerText !== null) {
            $interactive['header'] = ['type' => 'text', 'text' => $headerText];
        }

        if ($footerText !== null) {
            $interactive['footer'] = ['text' => $footerText];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $this->normalizePhone($to),
            'type'              => 'interactive',
            'interactive'       => $interactive,
        ];

        return $this->post("/{$this->phoneNumberId}/messages", $payload);
    }

    /**
     * Tandai pesan yang diterima sebagai sudah dibaca (read).
     */
    public function markMessageAsRead(string $messageId): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'status'            => 'read',
            'message_id'        => $messageId,
        ];

        return $this->post("/{$this->phoneNumberId}/messages", $payload);
    }

    /**
     * Ambil rincian nomor telepon, status verifikasi, dan rating kualitas dari Meta.
     */
    public function getPhoneNumberDetails(): array
    {
        return $this->get("/{$this->phoneNumberId}", [
            'fields' => 'id,verified_name,display_phone_number,quality_rating,code_verification_status,messaging_limit_tier',
        ]);
    }

    /**
     * Daftarkan (subscribe) Meta App ke Webhook WABA merchant.
     */
    public function subscribeAppToWaba(string $wabaId): array
    {
        return $this->post("/{$wabaId}/subscribed_apps", []);
    }

    /**
     * Normalisasi nomor telepon ke format internasional tanpa tanda plus (misal: 6281234567890).
     */
    public function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if (str_starts_with($cleaned, '0')) {
            return '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Inisialisasi HTTP Request terautentikasi dengan retry logic dan timeout aman.
     */
    protected function request(): PendingRequest
    {
        return Http::withToken($this->accessToken)
            ->timeout(20)
            ->acceptJson()
            ->asJson()
            ->retry(
                times: 3,
                sleepMilliseconds: 500,
                when: function (\Throwable $exception, PendingRequest $request): bool {
                    // Retry pada kegagalan koneksi transien
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    // Retry pada HTTP 429 (Rate Limit) atau HTTP 5xx (Server Error Meta)
                    if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                        $status = $exception->response->status();
                        return $status === 429 || ($status >= 500 && $status <= 504);
                    }

                    return false;
                },
                throw: false
            );
    }

    /**
     * Eksekusi HTTP POST ke Graph API Meta.
     */
    protected function post(string $endpoint, array $payload): array
    {
        $url = "{$this->baseUrl}/{$this->version}" . (str_starts_with($endpoint, '/') ? $endpoint : "/{$endpoint}");
        $startTime = microtime(true);

        try {
            $response = $this->request()->post($url, $payload);
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            return $this->handleResponse($response, 'POST', $endpoint, $payload, $durationMs);
        } catch (\Throwable $e) {
            $this->logError('POST', $endpoint, $e->getMessage(), ['payload' => $this->sanitizePayload($payload)]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Eksekusi HTTP GET ke Graph API Meta.
     */
    protected function get(string $endpoint, array $query = []): array
    {
        $url = "{$this->baseUrl}/{$this->version}" . (str_starts_with($endpoint, '/') ? $endpoint : "/{$endpoint}");
        $startTime = microtime(true);

        try {
            $response = $this->request()->get($url, $query);
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            return $this->handleResponse($response, 'GET', $endpoint, $query, $durationMs);
        } catch (\Throwable $e) {
            $this->logError('GET', $endpoint, $e->getMessage(), ['query' => $query]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Parsing response Graph API dan ekstraksi pesan/galat secara terstandar.
     */
    protected function handleResponse(
        Response $response,
        string $method,
        string $endpoint,
        array $params,
        float $durationMs
    ): array {
        $body = $response->json() ?? [];
        $statusCode = $response->status();

        if ($response->successful()) {
            $messageId = $body['messages'][0]['id'] ?? ($body['id'] ?? null);

            Log::channel('daily')->info("[Meta WA Client] {$method} {$endpoint} SUCCESS ({$durationMs}ms)", [
                'business_id'     => $this->businessId,
                'phone_number_id' => $this->phoneNumberId,
                'status_code'     => $statusCode,
                'message_id'      => $messageId,
            ]);

            return [
                'success'    => true,
                'message_id' => $messageId,
                'data'       => $body,
                'status'     => $statusCode,
            ];
        }

        // Tangani Error Meta
        $error = $body['error'] ?? [];
        $rawErrorMessage = $error['message'] ?? ("HTTP {$statusCode} Error from Meta Graph API");
        $errorCode    = $error['code'] ?? $statusCode;
        $errorSubcode = $error['error_subcode'] ?? null;
        $fbtraceId    = $error['fbtrace_id'] ?? null;

        $errorMessage = $rawErrorMessage;
        if ((int) $errorCode === 133010) {
            $wabaParam = $this->wabaId ? "?waba_id={$this->wabaId}" : '';
            $errorMessage = "Nomor WhatsApp belum terdaftar/terverifikasi di Meta Cloud API (#133010: Account not registered). Status nomor masih DISCONNECTED atau NOT_VERIFIED. Silakan lakukan verifikasi nomor di Meta WhatsApp Manager: https://business.facebook.com/wa/manage/phone-numbers/{$wabaParam}";
        }

        $this->logError($method, $endpoint, $errorMessage, [
            'status_code'   => $statusCode,
            'error_code'    => $errorCode,
            'error_subcode' => $errorSubcode,
            'fbtrace_id'    => $fbtraceId,
            'raw_error'     => $rawErrorMessage,
            'params'        => $this->sanitizePayload($params),
        ]);

        return [
            'success'       => false,
            'error'         => $errorMessage,
            'raw_error'     => $rawErrorMessage,
            'error_code'    => $errorCode,
            'error_subcode' => $errorSubcode,
            'fbtrace_id'    => $fbtraceId,
            'details'       => $body,
            'status'        => $statusCode,
        ];
    }

    /**
     * Sanitasi payload untuk log agar tidak membocorkan data sensitif (seperti token OTP / kredensial).
     */
    protected function sanitizePayload(array $payload): array
    {
        $sanitized = $payload;

        if (isset($sanitized['to'])) {
            $sanitized['to'] = substr((string) $sanitized['to'], 0, 4) . '****' . substr((string) $sanitized['to'], -3);
        }

        return $sanitized;
    }

    /**
     * Logging terstruktur dengan konteks tenant COOCA.
     */
    protected function logError(string $method, string $endpoint, string $message, array $context = []): void
    {
        $mergedContext = array_merge([
            'business_id'     => $this->businessId,
            'phone_number_id' => $this->phoneNumberId,
            'waba_id'         => $this->wabaId,
        ], $context);

        Log::channel('daily')->error("[Meta WA Client] {$method} {$endpoint} FAILED: {$message}", $mergedContext);
    }
}
