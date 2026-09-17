<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\WhatsApp;

use App\Domain\WhatsApp\CloudApi\WhatsAppClient;
use App\Http\Controllers\Controller;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppSession;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class MetaWhatsAppOnboardingController
 *
 * Mengelola alur Embedded Signup Meta WhatsApp Cloud API untuk merchant COOCA:
 * - Menyediakan konfigurasi SDK frontend (App ID, Config ID).
 * - Menukarkan authorization code dari Meta SDK menjadi Long-Lived Access Token.
 * - Mengambil data WABA ID, Phone Number ID, nama terverifikasi, dan rating kualitas.
 * - Mendaftarkan webhook aplikasi ke WABA (subscribed_apps).
 * - Menyimpan kredensial terenkripsi ke database terisolasi per-tenant.
 */
class MetaWhatsAppOnboardingController extends Controller
{
    protected string $graphApiBaseUrl;
    protected string $apiVersion;

    public function __construct()
    {
        $this->graphApiBaseUrl = (string) (\App\Models\SystemSetting::get('meta_wa_graph_url') ?: config('services.meta_whatsapp.graph_url', 'https://graph.facebook.com'));
        $this->apiVersion = (string) (\App\Models\SystemSetting::get('meta_wa_graph_version') ?: config('services.meta_whatsapp.version', 'v21.0'));
    }

    /**
     * Konfigurasi Meta Embedded Signup untuk SDK JavaScript di frontend.
     */
    public function getSignupConfig(): JsonResponse
    {
        $business = Context::requireBusiness();

        $appId    = (string) (\App\Models\SystemSetting::get('meta_wa_app_id') ?: config('services.meta_whatsapp.app_id', ''));
        $configId = (string) (\App\Models\SystemSetting::get('meta_wa_config_id') ?: config('services.meta_whatsapp.config_id', ''));

        if (empty($appId)) {
            return response()->json([
                'success' => false,
                'error'   => 'Konfigurasi Meta App ID belum diatur di server.',
            ], 500);
        }

        return response()->json([
            'success'   => true,
            'app_id'    => $appId,
            'config_id' => $configId,
            'version'   => $this->apiVersion,
            'business'  => [
                'id'   => $business->id,
                'name' => $business->name,
            ],
        ]);
    }

    /**
     * Menukar authorization code dari Meta Embedded Signup menjadi Access Token resmi.
     */
    public function exchangeCode(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'code'            => ['required', 'string'],
            'waba_id'         => ['nullable', 'string', 'max:100'],
            'phone_number_id' => ['nullable', 'string', 'max:100'],
        ]);

        $appId     = (string) (\App\Models\SystemSetting::get('meta_wa_app_id') ?: config('services.meta_whatsapp.app_id', ''));
        $appSecret = (string) (\App\Models\SystemSetting::get('meta_wa_app_secret') ?: config('services.meta_whatsapp.app_secret', ''));

        if (empty($appId) || empty($appSecret)) {
            return response()->json([
                'success' => false,
                'error'   => 'Kredensial Meta App ID atau App Secret belum dikonfigurasi.',
            ], 500);
        }

        $code = trim($validated['code']);

        try {
            // 1. Tukar authorization code menjadi long-lived access token
            $tokenUrl = "{$this->graphApiBaseUrl}/{$this->apiVersion}/oauth/access_token";
            $tokenResponse = Http::timeout(15)
                ->asForm()
                ->post($tokenUrl, [
                    'client_id'     => $appId,
                    'client_secret' => $appSecret,
                    'code'          => $code,
                ]);

            if (! $tokenResponse->successful()) {
                $errorMsg = $tokenResponse->json('error.message') ?? 'Gagal menukarkan code otorisasi Meta.';
                Log::channel('daily')->error("[Meta Onboarding] Gagal tukar token untuk bisnis {$business->id}: {$errorMsg}", [
                    'details' => $tokenResponse->json(),
                ]);

                return response()->json([
                    'success' => false,
                    'error'   => $errorMsg,
                ], 400);
            }

            $tokenData    = $tokenResponse->json();
            $accessToken  = (string) ($tokenData['access_token'] ?? '');
            $tokenType    = (string) ($tokenData['token_type'] ?? 'Bearer');
            $expiresIn    = $tokenData['expires_in'] ?? null;
            $expiresAt    = $expiresIn ? Carbon::now()->addSeconds((int) $expiresIn) : null;

            if (empty($accessToken)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Token akses tidak ditemukan dalam respons Meta.',
                ], 400);
            }

            // 2. Tentukan WABA ID (dari parameter request atau periksa debug_token)
            $wabaId = ! empty($validated['waba_id']) ? trim($validated['waba_id']) : null;
            $phoneNumberId = ! empty($validated['phone_number_id']) ? trim($validated['phone_number_id']) : null;

            if (empty($wabaId)) {
                $wabaId = $this->resolveWabaIdFromToken($accessToken, $appId, $appSecret);
            }

            if (empty($wabaId)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Tidak dapat mendeteksi WhatsApp Business Account (WABA) ID dari akun Meta Anda.',
                ], 422);
            }

            // 3. Ambil daftar nomor telepon yang terdaftar pada WABA tersebut
            $phoneDetails = $this->resolvePhoneDetails($accessToken, $wabaId, $phoneNumberId);

            if (! $phoneDetails) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Tidak ditemukan nomor telepon WhatsApp Business aktif pada akun Meta Anda.',
                ], 422);
            }

            $resolvedPhoneId          = $phoneDetails['id'];
            $displayPhoneNumber      = $phoneDetails['display_phone_number'] ?? null;
            $verifiedName             = $phoneDetails['verified_name'] ?? $business->name;
            $qualityRating            = $phoneDetails['quality_rating'] ?? 'UNKNOWN';
            $codeVerificationStatus   = $phoneDetails['code_verification_status'] ?? null;
            $messagingLimitTier       = $phoneDetails['messaging_limit_tier'] ?? 'TIER_50';
            $normalizedPhone          = preg_replace('/[^0-9]/', '', (string) ($displayPhoneNumber ?? '')) ?? '';

            // 4. Daftarkan (subscribe) aplikasi COOCA ke Webhook WABA merchant
            $client = WhatsAppClient::withCredentials($accessToken, $resolvedPhoneId, $wabaId, $business->id);
            $subResponse = $client->subscribeAppToWaba($wabaId);

            Log::channel('daily')->info("[Meta Onboarding] Subscribed App to WABA {$wabaId}", [
                'business_id' => $business->id,
                'result'      => $subResponse,
            ]);

            // 5. Simpan / Perbarui WhatsAppAccount untuk merchant ini
            $account = WhatsAppAccount::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'waba_id'                  => $wabaId,
                    'phone_number_id'          => $resolvedPhoneId,
                    'phone_number'             => $normalizedPhone,
                    'display_phone_number'     => $displayPhoneNumber,
                    'verified_name'            => $verifiedName,
                    'quality_rating'           => $qualityRating,
                    'code_verification_status' => $codeVerificationStatus,
                    'messaging_limit_tier'     => $messagingLimitTier,
                    'access_token'             => $accessToken, // Otomatis terenkripsi oleh model cast
                    'token_type'               => $tokenType,
                    'token_expires_at'         => $expiresAt,
                    'status'                   => 'active',
                    'webhook_verified_at'      => now(),
                    'metadata'                 => [
                        'token_info'    => $tokenData,
                        'phone_details' => $phoneDetails,
                    ],
                ]
            );

            // 6. Sinkronisasi dengan konfigurasi WhatsAppSession yang ada (backward compatibility)
            WhatsAppSession::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'session_id'            => 'biz_' . str_replace('-', '', substr($business->id, 0, 8)),
                    'provider'              => 'meta_cloud',
                    'status'                => 'connected',
                    'meta_phone_number_id'  => $resolvedPhoneId,
                    'meta_waba_id'          => $wabaId,
                    'phone_number'          => $normalizedPhone,
                    'last_connected_at'     => now(),
                    'is_active'             => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Integrasi WhatsApp Cloud API resmi Meta berhasil diaktifkan!',
                'account' => [
                    'id'                   => $account->id,
                    'verified_name'        => $account->verified_name,
                    'display_phone_number' => $account->display_phone_number,
                    'quality_rating'       => $account->quality_rating,
                    'messaging_limit_tier' => $account->messaging_limit_tier,
                    'status'               => $account->status,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::channel('daily')->error("[Meta Onboarding] Exception: {$e->getMessage()}", [
                'business_id' => $business->id,
                'trace'       => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Terjadi kesalahan sistem saat menghubungkan akun Meta: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memeriksa status akun WhatsApp Cloud API aktif milik merchant.
     */
    public function getStatus(): JsonResponse
    {
        $business = Context::requireBusiness();

        $account = WhatsAppAccount::where('business_id', $business->id)->first();

        if (! $account) {
            return response()->json([
                'success'   => true,
                'connected' => false,
                'account'   => null,
            ]);
        }

        // Ambil pembaruan status live langsung dari Graph API Meta jika aktif
        $liveDetails = null;
        if ($account->isActive()) {
            try {
                $client = WhatsAppClient::forAccount($account);
                $liveResult = $client->getPhoneNumberDetails();

                if ($liveResult['success'] ?? false) {
                    $liveData = $liveResult['data'] ?? [];
                    $account->update([
                        'verified_name'            => $liveData['verified_name'] ?? $account->verified_name,
                        'display_phone_number'     => $liveData['display_phone_number'] ?? $account->display_phone_number,
                        'quality_rating'           => $liveData['quality_rating'] ?? $account->quality_rating,
                        'code_verification_status' => $liveData['code_verification_status'] ?? $account->code_verification_status,
                        'messaging_limit_tier'     => $liveData['messaging_limit_tier'] ?? $account->messaging_limit_tier,
                    ]);
                    $liveDetails = $liveData;
                }
            } catch (\Throwable $e) {
                Log::channel('daily')->warning("[Meta Onboarding] Gagal membaca status live dari Meta: {$e->getMessage()}");
            }
        }

        return response()->json([
            'success'   => true,
            'connected' => $account->isActive(),
            'account'   => [
                'id'                   => $account->id,
                'waba_id'              => $account->waba_id,
                'phone_number_id'      => $account->phone_number_id,
                'display_phone_number' => $account->display_phone_number,
                'verified_name'        => $account->verified_name,
                'quality_rating'       => $account->quality_rating,
                'messaging_limit_tier' => $account->messaging_limit_tier,
                'status'               => $account->status,
                'token_expired'        => $account->isTokenExpired(),
                'webhook_verified_at'  => $account->webhook_verified_at?->toIso8601String(),
                'live_details'         => $liveDetails,
            ],
        ]);
    }

    /**
     * Memutuskan integrasi WhatsApp Cloud API merchant secara aman.
     */
    public function disconnect(): JsonResponse
    {
        $business = Context::requireBusiness();

        $account = WhatsAppAccount::where('business_id', $business->id)->first();
        if ($account) {
            $account->update([
                'status' => 'disconnected',
            ]);
        }

        $session = WhatsAppSession::where('business_id', $business->id)->first();
        if ($session && $session->provider === 'meta_cloud') {
            $session->update([
                'status' => 'disconnected',
            ]);
        }

        Log::channel('daily')->info("[Meta Onboarding] Merchant {$business->id} memutuskan integrasi Meta WhatsApp.");

        return response()->json([
            'success' => true,
            'message' => 'Integrasi WhatsApp Cloud API Meta berhasil dinonaktifkan.',
        ]);
    }

    /**
     * Resolusi WABA ID dari debug_token Graph API.
     */
    protected function resolveWabaIdFromToken(string $accessToken, string $appId, string $appSecret): ?string
    {
        $appToken = "{$appId}|{$appSecret}";
        $url = "{$this->graphApiBaseUrl}/debug_token";

        $response = Http::timeout(10)->get($url, [
            'input_token'  => $accessToken,
            'access_token' => $appToken,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json('data') ?? [];
        $granularScopes = $data['granular_scopes'] ?? [];

        foreach ($granularScopes as $scopeItem) {
            $scope = $scopeItem['scope'] ?? '';
            if (in_array($scope, ['whatsapp_business_management', 'whatsapp_business_messaging'], true)) {
                $targetIds = $scopeItem['target_ids'] ?? [];
                if (! empty($targetIds[0])) {
                    return (string) $targetIds[0];
                }
            }
        }

        return null;
    }

    /**
     * Resolusi nomor telepon WhatsApp yang terdaftar pada WABA.
     */
    protected function resolvePhoneDetails(string $accessToken, string $wabaId, ?string $preferredPhoneId = null): ?array
    {
        $url = "{$this->graphApiBaseUrl}/{$this->apiVersion}/{$wabaId}/phone_numbers";

        $response = Http::withToken($accessToken)
            ->timeout(10)
            ->get($url, [
                'fields' => 'id,display_phone_number,verified_name,quality_rating,code_verification_status,messaging_limit_tier',
            ]);

        if (! $response->successful()) {
            return null;
        }

        $phoneList = $response->json('data') ?? [];

        if (empty($phoneList)) {
            return null;
        }

        if (! empty($preferredPhoneId)) {
            foreach ($phoneList as $phone) {
                if (($phone['id'] ?? '') === $preferredPhoneId) {
                    return $phone;
                }
            }
        }

        // Default ambil nomor pertama yang terdaftar
        return $phoneList[0];
    }
}
