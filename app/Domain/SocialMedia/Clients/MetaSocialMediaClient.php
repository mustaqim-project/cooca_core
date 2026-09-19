<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia\Clients;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaSocialMediaClient
{
    protected string $appId;
    protected string $appSecret;
    protected string $graphVersion;
    protected string $graphUrl;

    public function __construct(
        ?string $appId = null,
        ?string $appSecret = null,
        ?string $graphVersion = null,
        ?string $graphUrl = null
    ) {
        $this->appId = $appId ?? (string) (SystemSetting::get('social_media_app_id') ?: config('services.meta_social.app_id', ''));
        $this->appSecret = $appSecret ?? (string) (SystemSetting::get('social_media_app_secret') ?: config('services.meta_social.app_secret', ''));
        $this->graphVersion = $graphVersion ?? (string) (SystemSetting::get('social_media_graph_version') ?: config('services.meta_social.graph_version', 'v21.0'));
        $this->graphUrl = rtrim($graphUrl ?? (string) (SystemSetting::get('social_media_graph_url') ?: config('services.meta_social.graph_url', 'https://graph.facebook.com')), '/');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->appId) && ! empty($this->appSecret);
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    public function getGraphVersion(): string
    {
        return $this->graphVersion;
    }

    public function getGraphUrl(): string
    {
        return $this->graphUrl;
    }

    protected function endpoint(string $path, ?string $token = null): string
    {
        $cleanPath = ltrim($path, '/');
        if ($token && str_starts_with($token, 'IGAA')) {
            return "https://graph.instagram.com/{$this->graphVersion}/{$cleanPath}";
        }

        return "{$this->graphUrl}/{$this->graphVersion}/{$cleanPath}";
    }

    /**
     * Generate Facebook Login for Business dialog URL.
     */
    public function getLoginUrl(string $redirectUri, string $state): string
    {
        $scope = implode(',', [
            'pages_show_list',
            'pages_read_engagement',
            'pages_manage_posts',
            'pages_messaging',
            'instagram_basic',
            'instagram_content_publish',
            'instagram_manage_messages',
        ]);

        $params = http_build_query([
            'client_id'     => $this->appId,
            'redirect_uri'  => $redirectUri,
            'state'         => $state,
            'scope'         => $scope,
            'response_type' => 'code',
        ]);

        return "https://www.facebook.com/{$this->graphVersion}/dialog/oauth?{$params}";
    }

    /**
     * Exchange short-lived authorization code for user access token.
     */
    public function exchangeCodeForUserToken(string $code, string $redirectUri): array
    {
        $response = Http::asJson()->get($this->endpoint('oauth/access_token'), [
            'client_id'     => $this->appId,
            'client_secret' => $this->appSecret,
            'redirect_uri'  => $redirectUri,
            'code'          => $code,
        ]);

        if (! $response->successful()) {
            Log::error('Meta OAuth exchange code failed', ['body' => $response->body()]);
            throw new \RuntimeException($response->json('error.message') ?? 'Gagal menukar kode otorisasi Meta.');
        }

        return $response->json();
    }

    /**
     * Exchange user token for long-lived user access token (valid 60 days).
     */
    public function exchangeTokenForLongLived(string $userToken): array
    {
        $response = Http::asJson()->get($this->endpoint('oauth/access_token'), [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => $this->appId,
            'client_secret'     => $this->appSecret,
            'fb_exchange_token' => $userToken,
        ]);

        if (! $response->successful()) {
            Log::error('Meta Long-Lived Token exchange failed', ['body' => $response->body()]);
            throw new \RuntimeException($response->json('error.message') ?? 'Gagal memperpanjang masa aktif token Meta.');
        }

        return $response->json();
    }

    /**
     * Fetch user's manageable Facebook Pages with permanent Page Tokens & linked Instagram Accounts.
     */
    public function getManageablePages(string $userAccessToken): array
    {
        $fields = 'id,name,category,access_token,tasks,picture{url},instagram_business_account{id,username,name,profile_picture_url}';

        $response = Http::asJson()->get($this->endpoint('me/accounts'), [
            'fields'       => $fields,
            'access_token' => $userAccessToken,
        ]);

        if (! $response->successful()) {
            Log::error('Meta getManageablePages failed', ['body' => $response->body()]);
            throw new \RuntimeException($response->json('error.message') ?? 'Gagal mengambil daftar halaman Facebook.');
        }

        return $response->json('data') ?? [];
    }

    /**
     * Publish Facebook Page post (photo or feed post).
     */
    public function publishFacebookPost(string $pageId, string $pageToken, string $message, ?string $link = null, ?string $imageUrl = null): array
    {
        if (! empty($imageUrl)) {
            $response = Http::asForm()->post($this->endpoint("{$pageId}/photos"), [
                'url'          => $imageUrl,
                'caption'      => $message,
                'access_token' => $pageToken,
            ]);
        } else {
            $payload = [
                'message'      => $message,
                'access_token' => $pageToken,
            ];
            if (! empty($link)) {
                $payload['link'] = $link;
            }

            $response = Http::asForm()->post($this->endpoint("{$pageId}/feed"), $payload);
        }

        if (! $response->successful()) {
            Log::error('Meta publishFacebookPost failed', ['body' => $response->body()]);
            throw new \RuntimeException($response->json('error.message') ?? 'Gagal mempublikasikan postingan ke Facebook.');
        }

        return $response->json();
    }

    /**
     * Publish video to Facebook Page (/{page-id}/videos).
     */
    public function publishFacebookVideo(string $pageId, string $pageToken, string $description, string $videoUrl, ?string $title = null): array
    {
        $payload = [
            'file_url'     => $videoUrl,
            'description'  => $description,
            'access_token' => $pageToken,
        ];
        if (! empty($title)) {
            $payload['title'] = $title;
        }

        $response = Http::asForm()->post($this->endpoint("{$pageId}/videos"), $payload);

        if (! $response->successful()) {
            Log::error('Meta publishFacebookVideo failed', ['body' => $response->body()]);
            throw new \RuntimeException($response->json('error.message') ?? 'Gagal mempublikasikan video ke Facebook Page.');
        }

        return $response->json();
    }

    /**
     * Wait for an Instagram media container to finish processing before publishing.
     */
    public function waitForMediaContainerReady(string $containerId, string $pageToken, int $maxAttempts = 8, int $sleepSeconds = 2): void
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $res = Http::get($this->endpoint($containerId, $pageToken), [
                'fields'       => 'status_code,status',
                'access_token' => $pageToken,
            ]);

            if ($res->successful()) {
                $statusCode = strtoupper((string) ($res->json('status_code') ?? ''));
                if ($statusCode === 'FINISHED') {
                    return;
                }
                if ($statusCode === 'ERROR' || $statusCode === 'EXPIRED') {
                    throw new \RuntimeException('Gagal memproses media di Instagram: ' . ($res->json('status') ?? $statusCode));
                }
            }

            if ($attempt < $maxAttempts) {
                sleep($sleepSeconds);
            }
        }
    }

    /**
     * Publish Instagram post (Photo, Video, or Reels) using 2-step media container process.
     */
    public function publishInstagramPost(string $igUserId, string $pageToken, string $caption, string $mediaUrl, string $mediaType = 'IMAGE'): array
    {
        $normalizedType = strtoupper($mediaType);
        $payload = [
            'caption'      => $caption,
            'access_token' => $pageToken,
        ];

        if ($normalizedType === 'REELS' || $normalizedType === 'VIDEO') {
            $payload['media_type'] = 'REELS';
            $payload['video_url'] = $mediaUrl;
            $payload['share_to_feed'] = true;
        } else {
            $payload['image_url'] = $mediaUrl;
        }

        // Step 1: Create media container
        $containerResponse = Http::asForm()->post($this->endpoint("{$igUserId}/media", $pageToken), $payload);

        if (! $containerResponse->successful()) {
            Log::error('Meta create IG container failed', ['body' => $containerResponse->body()]);
            throw new \RuntimeException($containerResponse->json('error.message') ?? 'Gagal membuat kontainer media Instagram.');
        }

        $creationId = $containerResponse->json('id');
        if (empty($creationId)) {
            throw new \RuntimeException('Creation ID Instagram tidak ditemukan.');
        }

        // For video / reels, wait until container status is FINISHED
        if ($normalizedType === 'REELS' || $normalizedType === 'VIDEO') {
            $this->waitForMediaContainerReady($creationId, $pageToken);
        }

        // Step 2: Publish media container
        $publishResponse = Http::asForm()->post($this->endpoint("{$igUserId}/media_publish", $pageToken), [
            'creation_id'  => $creationId,
            'access_token' => $pageToken,
        ]);

        if (! $publishResponse->successful()) {
            Log::error('Meta publish IG container failed', ['body' => $publishResponse->body()]);
            throw new \RuntimeException($publishResponse->json('error.message') ?? 'Gagal mempublikasikan media Instagram.');
        }

        return $publishResponse->json();
    }

    /**
     * Publish Instagram Carousel (2 to 10 items) using child containers + carousel container.
     *
     * @param  list<array<string, mixed>|string>  $items
     */
    public function publishInstagramCarousel(string $igUserId, string $pageToken, string $caption, array $items): array
    {
        if (count($items) < 2 || count($items) > 10) {
            throw new \InvalidArgumentException('Instagram Carousel mewajibkan antara 2 hingga 10 berkas media.');
        }

        $childContainerIds = [];

        // 1. Create child container for each item
        foreach ($items as $item) {
            $url = is_array($item) ? ($item['media_url'] ?? $item['url'] ?? '') : (string) $item;
            $type = is_array($item) ? strtolower((string) ($item['media_type'] ?? 'image')) : 'image';
            $isVideo = $type === 'video' || preg_match('/\.(mp4|mov)$/i', $url);

            $payload = [
                'is_carousel_item' => true,
                'access_token'     => $pageToken,
            ];

            if ($isVideo) {
                $payload['media_type'] = 'VIDEO';
                $payload['video_url'] = $url;
            } else {
                $payload['image_url'] = $url;
            }

            $res = Http::asForm()->post($this->endpoint("{$igUserId}/media", $pageToken), $payload);
            if (! $res->successful() || empty($res->json('id'))) {
                Log::error('Meta create IG carousel child container failed', ['body' => $res->body()]);
                throw new \RuntimeException($res->json('error.message') ?? 'Gagal membuat kontainer item carousel Instagram.');
            }

            $childId = (string) $res->json('id');
            if ($isVideo) {
                $this->waitForMediaContainerReady($childId, $pageToken);
            }
            $childContainerIds[] = $childId;
        }

        // 2. Create parent carousel container
        $carouselRes = Http::asForm()->post($this->endpoint("{$igUserId}/media", $pageToken), [
            'media_type'   => 'CAROUSEL',
            'children'     => implode(',', $childContainerIds),
            'caption'      => $caption,
            'access_token' => $pageToken,
        ]);

        if (! $carouselRes->successful() || empty($carouselRes->json('id'))) {
            Log::error('Meta create IG carousel parent failed', ['body' => $carouselRes->body()]);
            throw new \RuntimeException($carouselRes->json('error.message') ?? 'Gagal membuat kontainer induk Carousel Instagram.');
        }

        $carouselCreationId = (string) $carouselRes->json('id');

        // 3. Publish parent carousel container
        $publishRes = Http::asForm()->post($this->endpoint("{$igUserId}/media_publish", $pageToken), [
            'creation_id'  => $carouselCreationId,
            'access_token' => $pageToken,
        ]);

        if (! $publishRes->successful()) {
            Log::error('Meta publish IG carousel failed', ['body' => $publishRes->body()]);
            throw new \RuntimeException($publishRes->json('error.message') ?? 'Gagal mempublikasikan Carousel Instagram.');
        }

        return $publishRes->json();
    }

    /**
     * Publish Threads post using 2-step container process (Text, Image, or Video).
     */
    public function publishThreadsPost(string $threadsUserId, string $token, string $text, ?string $mediaUrl = null, string $mediaType = 'TEXT'): array
    {
        $normalizedType = strtoupper($mediaType);
        $payload = [
            'media_type'   => $normalizedType,
            'text'         => $text,
            'access_token' => $token,
        ];

        if (! empty($mediaUrl)) {
            if ($normalizedType === 'VIDEO') {
                $payload['video_url'] = $mediaUrl;
            } else {
                $payload['media_type'] = 'IMAGE';
                $payload['image_url'] = $mediaUrl;
            }
        }

        // Step 1: Create Threads container
        $containerResponse = Http::asForm()->post($this->endpoint("{$threadsUserId}/threads"), $payload);

        if (! $containerResponse->successful()) {
            Log::error('Meta create Threads container failed', ['body' => $containerResponse->body()]);
            throw new \RuntimeException($containerResponse->json('error.message') ?? 'Gagal membuat postingan Threads.');
        }

        $creationId = $containerResponse->json('id');

        // Step 2: Publish Threads container
        $publishResponse = Http::asForm()->post($this->endpoint("{$threadsUserId}/threads_publish"), [
            'creation_id'  => $creationId,
            'access_token' => $token,
        ]);

        if (! $publishResponse->successful()) {
            Log::error('Meta publish Threads container failed', ['body' => $publishResponse->body()]);
            throw new \RuntimeException($publishResponse->json('error.message') ?? 'Gagal mempublikasikan postingan Threads.');
        }

        return $publishResponse->json();
    }

    /**
     * Reply to a Facebook or Instagram comment.
     */
    public function replyComment(string $platform, string $commentId, string $pageToken, string $message): array
    {
        $path = strtolower($platform) === 'instagram' ? "{$commentId}/replies" : "{$commentId}/comments";

        $response = Http::asForm()->post($this->endpoint($path, $pageToken), [
            'message'      => $message,
            'access_token' => $pageToken,
        ]);

        if (! $response->successful()) {
            Log::error('Meta replyComment failed', ['body' => $response->body()]);
            throw new \RuntimeException($response->json('error.message') ?? 'Gagal mengirim balasan komentar.');
        }

        return $response->json();
    }

    /**
     * Fetch post metrics & insights from Meta Graph API.
     */
    public function getPostInsights(string $platform, string $platformPostId, string $token): array
    {
        $metrics = [
            'impressions' => 0,
            'reach'       => 0,
            'likes'       => 0,
            'comments'    => 0,
            'shares'      => 0,
            'saved'       => 0,
        ];

        try {
            if (strtolower($platform) === 'instagram') {
                $response = Http::asJson()->get($this->endpoint("{$platformPostId}/insights"), [
                    'metric'       => 'impressions,reach,saved',
                    'access_token' => $token,
                ]);
            } else {
                $response = Http::asJson()->get($this->endpoint("{$platformPostId}/insights"), [
                    'metric'       => 'post_impressions,post_engaged_users',
                    'access_token' => $token,
                ]);
            }

            if ($response->successful()) {
                foreach ($response->json('data') ?? [] as $metricItem) {
                    $name = $metricItem['name'] ?? '';
                    $val = (int) ($metricItem['values'][0]['value'] ?? 0);

                    if (in_array($name, ['impressions', 'post_impressions'], true)) {
                        $metrics['impressions'] = $val;
                    } elseif (in_array($name, ['reach', 'post_engaged_users'], true)) {
                        $metrics['reach'] = $val;
                    } elseif ($name === 'saved') {
                        $metrics['saved'] = $val;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed fetching post insights', ['error' => $e->getMessage()]);
        }

        return $metrics;
    }

    /**
     * Verify HMAC-SHA256 signature from Meta Webhook payload.
     */
    public function verifyWebhookSignature(string $payload, ?string $signatureHeader, ?string $secret = null): bool
    {
        if (empty($signatureHeader)) {
            return false;
        }

        $appSecret = $secret ?: $this->appSecret;
        if (empty($appSecret)) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($expected, $signatureHeader);
    }
}
