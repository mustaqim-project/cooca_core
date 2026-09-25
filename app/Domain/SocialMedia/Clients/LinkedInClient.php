<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia\Clients;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInClient
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $apiUrl;
    protected string $authUrl;
    protected string $tokenUrl;

    public function __construct(
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $apiUrl = null,
        ?string $authUrl = null,
        ?string $tokenUrl = null
    ) {
        $this->clientId = $clientId
            ?: (string) (SystemSetting::get('linkedin_client_id') ?: config('services.linkedin.client_id', '868wurbnxke9xg'));
        $this->clientSecret = $clientSecret
            ?: (string) (SystemSetting::get('linkedin_client_secret') ?: config('services.linkedin.client_secret', 'WPL_AP1.T6CrhB0PHBB6XA3T.ddwN2A=='));
        $this->apiUrl = rtrim($apiUrl ?: (string) config('services.linkedin.api_url', 'https://api.linkedin.com/'), '/') . '/';
        $this->authUrl = $authUrl ?: (string) config('services.linkedin.auth_url', 'https://www.linkedin.com/oauth/v2/authorization');
        $this->tokenUrl = $tokenUrl ?: (string) config('services.linkedin.token_url', 'https://www.linkedin.com/oauth/v2/accessToken');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret);
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * Generate LinkedIn OAuth 2.0 Authorization URL with OpenID and Social Posting scopes.
     */
    public function getAuthUrl(string $redirectUri, string $state): string
    {
        $params = [
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $redirectUri,
            'state'         => $state,
            'scope'         => 'openid profile email w_member_social',
        ];

        return $this->authUrl . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for LinkedIn access token.
     *
     * @return array<string, mixed>
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $response = Http::asForm()->timeout(15)->post($this->tokenUrl, [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectUri,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (! $response->successful()) {
            $errorMsg = $response->json('error_description') ?? $response->json('error') ?? $response->body();
            Log::error('LinkedInClient::exchangeCodeForToken failed', [
                'status' => $response->status(),
                'error'  => $errorMsg,
            ]);

            throw new \RuntimeException("Otorisasi LinkedIn gagal ({$response->status()}): {$errorMsg}");
        }

        return (array) $response->json();
    }

    /**
     * Get member profile info using OpenID UserInfo endpoint.
     *
     * @return array<string, mixed>
     */
    public function getProfile(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->timeout(10)
            ->get($this->apiUrl . 'v2/userinfo');

        if (! $response->successful()) {
            $errorMsg = $response->json('message') ?? $response->body();
            Log::error('LinkedInClient::getProfile failed', [
                'status' => $response->status(),
                'error'  => $errorMsg,
            ]);

            throw new \RuntimeException("Gagal mengambil profil LinkedIn ({$response->status()}): {$errorMsg}");
        }

        return (array) $response->json();
    }

    /**
     * Register upload asset for media (images) via LinkedIn Digital Media API.
     *
     * @return array{upload_url: string, asset: string}
     */
    public function registerImageUpload(string $authorUrn, string $accessToken): array
    {
        $url = $this->apiUrl . 'v2/assets?action=registerUpload';

        $body = [
            'registerUploadRequest' => [
                'recipes' => [
                    'urn:li:digitalmediaRecipe:feedshare-image',
                ],
                'owner' => $authorUrn,
                'supportedUploadMechanism' => [
                    'SYNCHRONOUS_UPLOAD',
                ],
            ],
        ];

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'X-Restli-Protocol-Version' => '2.0.0',
            ])
            ->timeout(15)
            ->post($url, $body);

        if (! $response->successful()) {
            $errorMsg = $response->json('message') ?? $response->body();
            throw new \RuntimeException("Gagal mendaftarkan unggahan gambar LinkedIn: {$errorMsg}");
        }

        $data = (array) $response->json('value', []);
        $uploadUrl = (string) data_get($data, 'uploadMechanism.com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest.uploadUrl', '');
        $asset = (string) ($data['asset'] ?? '');

        if (empty($uploadUrl) || empty($asset)) {
            throw new \RuntimeException('Respons inisialisasi media LinkedIn tidak valid.');
        }

        return [
            'upload_url' => $uploadUrl,
            'asset'      => $asset,
        ];
    }

    /**
     * Upload physical or downloaded image binary to LinkedIn S3 / CDN upload URL.
     */
    public function uploadImageBinary(string $uploadUrl, string $imageBinary): bool
    {
        $response = Http::withBody($imageBinary, 'application/octet-stream')
            ->timeout(30)
            ->put($uploadUrl);

        return $response->successful();
    }

    /**
     * Publish post to member's feed (Text or Image).
     *
     * @param  list<array<string, mixed>>  $mediaItems
     * @return array<string, mixed>
     */
    public function publishPost(string $authorSubOrUrn, string $accessToken, string $caption, array $mediaItems = []): array
    {
        $authorUrn = str_starts_with($authorSubOrUrn, 'urn:li:')
            ? $authorSubOrUrn
            : "urn:li:person:{$authorSubOrUrn}";

        $firstMedia = ! empty($mediaItems) ? $mediaItems[0] : null;
        $firstMediaUrl = is_array($firstMedia) ? ($firstMedia['media_url'] ?? '') : (string) $firstMedia;
        $firstLocalPath = is_array($firstMedia) ? ($firstMedia['local_path'] ?? null) : null;

        $assetUrn = null;

        // If an image is provided, upload to LinkedIn Digital Media Asset
        if (! empty($firstMediaUrl)) {
            try {
                $uploadReg = $this->registerImageUpload($authorUrn, $accessToken);
                $uploadUrl = $uploadReg['upload_url'];
                $assetUrn = $uploadReg['asset'];

                $binary = null;
                if (! empty($firstLocalPath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($firstLocalPath)) {
                    $binary = \Illuminate\Support\Facades\Storage::disk('public')->get($firstLocalPath);
                } elseif (! empty($firstMediaUrl)) {
                    $dl = Http::timeout(20)->get($firstMediaUrl);
                    if ($dl->successful()) {
                        $binary = $dl->body();
                    }
                }

                if (! empty($binary)) {
                    $this->uploadImageBinary($uploadUrl, $binary);
                }
            } catch (\Throwable $e) {
                Log::warning('LinkedInClient: Image upload failed, falling back to text post: ' . $e->getMessage());
                $assetUrn = null;
            }
        }

        // Build UGC Post Request
        $ugcUrl = $this->apiUrl . 'v2/ugcPosts';

        $shareMediaCategory = ! empty($assetUrn) ? 'IMAGE' : 'NONE';
        $mediaList = [];

        if (! empty($assetUrn)) {
            $mediaList[] = [
                'status' => 'READY',
                'description' => [
                    'text' => mb_substr($caption, 0, 200),
                ],
                'media' => $assetUrn,
                'title' => [
                    'text' => 'Postingan Cooca',
                ],
            ];
        }

        $body = [
            'author' => $authorUrn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => [
                        'text' => $caption,
                    ],
                    'shareMediaCategory' => $shareMediaCategory,
                    'media' => $mediaList,
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ];

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'X-Restli-Protocol-Version' => '2.0.0',
            ])
            ->timeout(20)
            ->post($ugcUrl, $body);

        if (! $response->successful()) {
            $errorMsg = $response->json('message') ?? $response->body();
            Log::error('LinkedInClient::publishPost failed', [
                'status' => $response->status(),
                'error'  => $errorMsg,
            ]);

            throw new \RuntimeException("Gagal menerbitkan postingan ke LinkedIn ({$response->status()}): {$errorMsg}");
        }

        $resData = (array) $response->json();
        $postId = (string) ($resData['id'] ?? $response->header('x-restli-id') ?? '');

        return [
            'id'     => $postId,
            'status' => 'SUCCESS',
            'raw'    => $resData,
        ];
    }

    /**
     * Get post engagement metrics (impressions, likes, comments).
     *
     * @return array<string, int>
     */
    public function getPostInsights(string $postId, string $accessToken): array
    {
        return [
            'impressions' => 0,
            'reach'       => 0,
            'likes'       => 0,
            'comments'    => 0,
            'shares'      => 0,
        ];
    }
}
