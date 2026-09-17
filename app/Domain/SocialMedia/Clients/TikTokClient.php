<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia\Clients;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokClient
{
    protected string $clientKey;

    protected string $clientSecret;

    protected string $apiUrl;

    protected string $authUrl;

    public function __construct(
        ?string $clientKey = null,
        ?string $clientSecret = null,
        ?string $apiUrl = null,
        ?string $authUrl = null
    ) {
        $this->clientKey = $clientKey ?? (string) (SystemSetting::get('tiktok_client_key') ?: config('services.tiktok.client_key', ''));
        $this->clientSecret = $clientSecret ?? (string) (SystemSetting::get('tiktok_client_secret') ?: config('services.tiktok.client_secret', ''));
        $this->apiUrl = rtrim($apiUrl ?? (string) (SystemSetting::get('tiktok_api_url') ?: config('services.tiktok.api_url', 'https://open.tiktokapis.com/v2/')), '/') . '/';
        $this->authUrl = $authUrl ?? (string) (SystemSetting::get('tiktok_auth_url') ?: config('services.tiktok.auth_url', 'https://www.tiktok.com/v2/auth/authorize/'));
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->clientKey) && ! empty($this->clientSecret);
    }

    public function endpoint(string $path): string
    {
        return $this->apiUrl . ltrim($path, '/');
    }

    /**
     * Generate official TikTok OAuth 2.0 authorization URL.
     *
     * @param  list<string>|null  $scopes
     */
    public function getAuthUrl(string $redirectUri, string $state, ?array $scopes = null): string
    {
        $defaultScopes = [
            'user.info.basic',
            'user.info.profile',
            'user.info.stats',
            'video.publish',
            'video.upload',
        ];

        $scopeList = implode(',', $scopes ?: $defaultScopes);

        $params = [
            'client_key'    => $this->clientKey,
            'scope'         => $scopeList,
            'response_type' => 'code',
            'redirect_uri'  => $redirectUri,
            'state'         => $state,
        ];

        return $this->authUrl . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for Access Token and Refresh Token.
     *
     * @return array<string, mixed>
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $response = Http::asForm()->post($this->endpoint('oauth/token/'), [
            'client_key'    => $this->clientKey,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $redirectUri,
        ]);

        if (! $response->successful() || ! empty($response->json('error'))) {
            $msg = $response->json('error_description') ?: ($response->json('message') ?? 'Gagal menukar kode otorisasi TikTok.');
            Log::error('TikTok exchangeCodeForToken failed', ['body' => $response->body()]);
            throw new \RuntimeException("TikTok OAuth Error: {$msg}");
        }

        $data = $response->json('data') ?: $response->json();
        if (empty($data['access_token'])) {
            throw new \RuntimeException('Access Token TikTok tidak ditemukan dalam respons.');
        }

        return $data;
    }

    /**
     * Refresh an expired or expiring Access Token using Refresh Token.
     *
     * @return array<string, mixed>
     */
    public function refreshToken(string $refreshToken): array
    {
        $response = Http::asForm()->post($this->endpoint('oauth/token/'), [
            'client_key'    => $this->clientKey,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        if (! $response->successful() || ! empty($response->json('error'))) {
            $msg = $response->json('error_description') ?: ($response->json('message') ?? 'Gagal memperbarui token TikTok.');
            Log::error('TikTok refreshToken failed', ['body' => $response->body()]);
            throw new \RuntimeException("TikTok Token Refresh Error: {$msg}");
        }

        $data = $response->json('data') ?: $response->json();
        if (empty($data['access_token'])) {
            throw new \RuntimeException('Token pembaruan TikTok tidak valid.');
        }

        return $data;
    }

    /**
     * Revoke access token.
     */
    public function revokeToken(string $token): array
    {
        $response = Http::asForm()->post($this->endpoint('oauth/revoke/'), [
            'client_key'    => $this->clientKey,
            'client_secret' => $this->clientSecret,
            'token'         => $token,
        ]);

        return $response->json() ?: [];
    }

    /**
     * Query creator info (creator avatar, username, privacy options, interaction limits).
     *
     * @return array<string, mixed>
     */
    public function getCreatorInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->post($this->endpoint('post/publish/creator_info/query/'), (object) []);

        if (! $response->successful()) {
            Log::warning('TikTok getCreatorInfo failed', ['body' => $response->body()]);

            return [];
        }

        return $response->json('data') ?: [];
    }

    /**
     * Direct Post: Publish video to TikTok using PULL_FROM_URL.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function publishVideo(string $accessToken, string $caption, string $videoUrl, array $options = []): array
    {
        $payload = [
            'post_info' => [
                'title'           => $caption,
                'privacy_level'   => $options['privacy_level'] ?? 'PUBLIC_TO_EVERYONE',
                'disable_duet'    => (bool) ($options['disable_duet'] ?? false),
                'disable_stitch'  => (bool) ($options['disable_stitch'] ?? false),
                'disable_comment' => (bool) ($options['disable_comment'] ?? false),
            ],
            'source_info' => [
                'source'    => 'PULL_FROM_URL',
                'video_url' => $videoUrl,
            ],
        ];

        $response = Http::withToken($accessToken)
            ->post($this->endpoint('post/publish/video/init/'), $payload);

        if (! $response->successful() || ! empty($response->json('error'))) {
            $err = $response->json('error.message') ?: ($response->json('message') ?? 'Gagal menginisialisasi posting video TikTok.');
            Log::error('TikTok publishVideo failed', ['body' => $response->body()]);
            throw new \RuntimeException("TikTok Video Direct Post Error: {$err}");
        }

        $data = $response->json('data') ?: $response->json();
        $publishId = $data['publish_id'] ?? null;

        if (empty($publishId)) {
            throw new \RuntimeException('Publish ID TikTok tidak ditemukan dalam respons.');
        }

        return $data;
    }

    /**
     * Direct Post: Publish Photo Album Mode to TikTok using PULL_FROM_URL.
     *
     * @param  list<string>          $imageUrls
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function publishPhoto(string $accessToken, string $caption, array $imageUrls, array $options = []): array
    {
        if (count($imageUrls) < 2) {
            throw new \InvalidArgumentException('TikTok Photo Mode membutuhkan minimal 2 gambar.');
        }

        $payload = [
            'post_info' => [
                'title'           => $caption,
                'description'     => $caption,
                'privacy_level'   => $options['privacy_level'] ?? 'PUBLIC_TO_EVERYONE',
                'disable_comment' => (bool) ($options['disable_comment'] ?? false),
                'auto_add_music'  => true,
            ],
            'source_info' => [
                'source'            => 'PULL_FROM_URL',
                'photo_cover_index' => 1,
                'photo_images'      => array_values($imageUrls),
            ],
            'post_mode'  => 'DIRECT_POST',
            'media_type' => 'PHOTO',
        ];

        $response = Http::withToken($accessToken)
            ->post($this->endpoint('post/publish/content/init/'), $payload);

        if (! $response->successful() || ! empty($response->json('error'))) {
            $err = $response->json('error.message') ?: ($response->json('message') ?? 'Gagal menginisialisasi posting foto TikTok.');
            Log::error('TikTok publishPhoto failed', ['body' => $response->body()]);
            throw new \RuntimeException("TikTok Photo Direct Post Error: {$err}");
        }

        $data = $response->json('data') ?: $response->json();
        $publishId = $data['publish_id'] ?? null;

        if (empty($publishId)) {
            throw new \RuntimeException('Publish ID TikTok tidak ditemukan dalam respons.');
        }

        return $data;
    }

    /**
     * Query status of a TikTok publish task.
     *
     * @return array<string, mixed>
     */
    public function getPublishStatus(string $accessToken, string $publishId): array
    {
        $response = Http::withToken($accessToken)
            ->post($this->endpoint('post/publish/status/fetch/'), [
                'publish_id' => $publishId,
            ]);

        if (! $response->successful()) {
            Log::warning('TikTok getPublishStatus failed', ['body' => $response->body()]);

            return ['status' => 'UNKNOWN'];
        }

        return $response->json('data') ?: [];
    }

    /**
     * Poll TikTok publish status until SUCCESS or FAILED.
     *
     * @return array<string, mixed>
     */
    public function waitForPublishFinished(
        string $accessToken,
        string $publishId,
        int $maxAttempts = 10,
        int $sleepSeconds = 2
    ): array {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $data = $this->getPublishStatus($accessToken, $publishId);
            $status = strtoupper((string) ($data['status'] ?? ''));

            if ($status === 'SUCCESS' || $status === 'PUBLISH_COMPLETE') {
                return $data;
            }

            if ($status === 'FAILED' || $status === 'FAIL') {
                $reason = $data['fail_reason'] ?? 'Gagal dipublikasikan oleh TikTok.';
                throw new \RuntimeException("TikTok Publish Failed: {$reason}");
            }

            if ($attempt < $maxAttempts) {
                sleep($sleepSeconds);
            }
        }

        // Return current status data if timeout reached (non-blocking for background queue)
        return $data ?? ['status' => 'PROCESSING', 'publish_id' => $publishId];
    }
}
